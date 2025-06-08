import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';
import { Stripe } from 'stripe';
import { AppleService } from './apple.service';

@Injectable()
export class SubscriptionBillingService {
  private stripe: Stripe;

  constructor(
    private readonly prisma: PrismaService,
    private readonly appleService: AppleService
  ) {
    this.stripe = new Stripe(process.env.STRIPE_SECRET_KEY || '', {
      apiVersion: '2025-05-28.basil',
    });
  }

  verifyStripeWebhook(body: string, signature: string) {
    return this.stripe.webhooks.constructEvent(
      body,
      signature,
      process.env.STRIPE_WEBHOOK_SECRET
    );
  }

  async userHasActivePremiumSubscription(userId: string): Promise<boolean> {
    const activeSubscription = await this.prisma.subscription.findFirst({
      where: {
        userId,
        stripeStatus: {
          in: ['ACTIVE', 'TRIALING'],
        },
        plan: {
          slug: {
            contains: 'premium',
          },
        },
      },
    });
    return !!activeSubscription;
  }

  async getPlans() {
    return this.prisma.plan.findMany();
  }

  async getUserSubscription(userId: string) {
    return this.prisma.subscription.findFirst({
      where: {
        userId,
        stripeStatus: {
          in: ['ACTIVE', 'TRIALING']
        }
      },
      include: {
        plan: true
      }
    });
  }

  async handleStripeEvent(event: any) {
    switch (event.type) {
      case 'checkout.session.completed':
        await this.handleCheckoutSessionCompleted(event);
        break;
      case 'customer.subscription.updated':
        if (event.data.object.trial_end && event.data.object.status === 'active') {
          await this.handleCustomerSubscriptionUpdatedTrialEnded(event);
        } else if (event.data.object.cancel_at_period_end) {
          await this.handleCustomerSubscriptionUpdatedCancel(event);
        }
        break;
      case 'invoice.payment_succeeded':
        await this.handleInvoicePaymentSucceeded(event);
        break;
      case 'invoice.payment_failed':
        await this.handleInvoicePaymentFailed(event);
        break;
      case 'customer.subscription.deleted':
        await this.handleCustomerSubscriptionDeleted(event);
        break;
      default:
        console.warn(`Unhandled Stripe event type: ${event.type}`);
    }
  }

  async handleCheckoutSessionCompleted(payload: any) {
    const customerId = payload.data.object.customer;
    const customerEmail = payload.data.object.customer_email;
    const subscriptionId = payload.data.object.subscription;

    if (!customerId || !customerEmail || !subscriptionId) {
      throw new NotFoundException('Missing data in checkout.session.completed event');
    }

    const user = await this.prisma.user.findUnique({
      where: { email: customerEmail }
    });

    if (!user) {
      throw new NotFoundException(`User not found for email: ${customerEmail}`);
    }

    await this.prisma.subscription.create({
      data: {
        name: 'default',
        stripeId: subscriptionId,
        stripeCustomerId: customerId,
        stripeStatus: 'TRIALING',
        userId: user.id
      }
    });

    if (!user.stripeId || user.stripeId !== customerId) {
      await this.prisma.user.update({
        where: { id: user.id },
        data: { stripeId: customerId }
      });
    }
  }

  async handleCustomerSubscriptionUpdatedTrialEnded(payload: any) {
    const subscriptionId = payload.data.object.id;
    if (!subscriptionId) {
      throw new NotFoundException('Missing subscription ID in customer.subscription.updated event');
    }

    await this.prisma.subscription.update({
      where: { stripeId: subscriptionId },
      data: {
        stripeStatus: 'ACTIVE',
        trialEndsAt: null,
        endsAt: null
      }
    });
  }

  async handleInvoicePaymentSucceeded(payload: any) {
    const subscriptionId = payload.data.object.subscription;
    if (!subscriptionId) {
      throw new NotFoundException('Missing subscription ID in invoice.payment_succeeded event');
    }

    const periodEnd = payload.data.object.lines.data[0].period.end;
    if (!periodEnd) {
      throw new NotFoundException('Missing period end in invoice.payment_succeeded event');
    }

    await this.prisma.subscription.update({
      where: { stripeId: subscriptionId },
      data: {
        endsAt: new Date(periodEnd * 1000)
      }
    });
  }

  async handleInvoicePaymentFailed(payload: any) {
    const subscriptionId = payload.data.object.subscription;
    if (!subscriptionId) {
      throw new NotFoundException('Missing subscription ID in invoice.payment_failed event');
    }

    await this.prisma.subscription.update({
      where: { stripeId: subscriptionId },
      data: { stripeStatus: 'PAST_DUE' }
    });
  }

  async handleCustomerSubscriptionUpdatedCancel(payload: any) {
    const subscriptionId = payload.data.object.id;
    if (!subscriptionId) {
      throw new NotFoundException('Missing subscription ID in customer.subscription.updated event');
    }

    const periodEnd = payload.data.object.current_period_end;
    if (!periodEnd) {
      throw new NotFoundException('Missing period end in customer.subscription.updated event');
    }

    await this.prisma.subscription.update({
      where: { stripeId: subscriptionId },
      data: {
        stripeStatus: 'CANCELED',
        endsAt: new Date(periodEnd * 1000)
      }
    });
  }

  async handleCustomerSubscriptionDeleted(payload: any) {
    const subscriptionId = payload.data.object.id;
    if (!subscriptionId) {
      throw new NotFoundException('Missing subscription ID in customer.subscription.deleted event');
    }

    await this.prisma.subscription.update({
      where: { stripeId: subscriptionId },
      data: {
        stripeStatus: 'CANCELED',
        endsAt: new Date()
      }
    });
  }

  async handleAppleNotification(jws: string, notificationType: string): Promise<void> {
    const payload = await this.appleService.decodeAndVerifyJWS(jws);
    
    switch (notificationType) {
      case 'DID_CHANGE_RENEWAL_STATUS':
        await this.handleDidChangeRenewalStatus(payload);
        break;
      default:
        console.warn(`Unhandled Apple notification type: ${notificationType}`);
    }
  }

  private async handleDidChangeRenewalStatus(payload: any): Promise<void> {
    const autoRenewStatus = payload?.data?.autoRenewStatus;
    const productId = payload?.data?.productId;
    
    if (autoRenewStatus === false && productId) {
      const subscription = await this.prisma.subscription.findFirst({
        where: { plan_product_id: productId }
      });

      if (subscription) {
        await this.prisma.subscription.update({
          where: { id: subscription.id },
          data: { status: 'CANCELED' }
        });
        console.log(`Canceled subscription ${subscription.id} for product ${productId}`);
      }
    }
  }

  async handleGoogleNotification(message: any) {
    try {
      const notificationType = message?.subscriptionNotification?.notificationType;
      const subscriptionId = message?.subscriptionNotification?.subscriptionId;
      
      if (!notificationType || !subscriptionId) {
        throw new Error('Invalid Google Play notification - missing required fields');
      }

      switch (notificationType) {
        case 'SUBSCRIPTION_PURCHASED':
        case 'SUBSCRIPTION_RENEWED':
          await this.prisma.subscription.update({
            where: { googlePlaySubscriptionId: subscriptionId },
            data: { status: 'ACTIVE' }
          });
          break;
        case 'SUBSCRIPTION_CANCELED':
        case 'SUBSCRIPTION_EXPIRED':
          await this.prisma.subscription.update({
            where: { googlePlaySubscriptionId: subscriptionId },
            data: { status: 'CANCELED' }
          });
          break;
        default:
          console.warn(`Unhandled Google Play notification type: ${notificationType}`);
      }
    } catch (error) {
      console.error('Error handling Google Play notification:', error);
      throw error;
    }
  }
}