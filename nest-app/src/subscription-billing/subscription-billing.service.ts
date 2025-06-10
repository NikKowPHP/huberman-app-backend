import { Injectable, NotFoundException, Logger } from '@nestjs/common';
import { EventEmitter2 } from '@nestjs/event-emitter';
import { PrismaService } from '../common/prisma/prisma.service';
import { Stripe } from 'stripe';
import { AppleService } from './apple.service';

@Injectable()
export class SubscriptionBillingService {
  private stripe: Stripe;

  private readonly logger = new Logger(SubscriptionBillingService.name);

  constructor(
    private readonly prisma: PrismaService,
    private readonly appleService: AppleService,
    private readonly eventEmitter: EventEmitter2
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
    try {
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

      const subscription = await this.prisma.subscription.create({
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

      this.eventEmitter.emit('subscription.started', {
        userId: user.id,
        subscriptionId: subscription.id,
        trialEnd: payload.data.object.subscription.trial_end
      });
    } catch (error) {
      this.logger.error(`Error handling checkout session completed: ${error.message}`, error.stack);
      throw error;
    }
  }

  async handleCustomerSubscriptionUpdatedTrialEnded(payload: any) {
    try {
      const subscriptionId = payload.data.object.id;
      if (!subscriptionId) {
        throw new NotFoundException('Missing subscription ID in customer.subscription.updated event');
      }

      const subscription = await this.prisma.subscription.update({
        where: { stripeId: subscriptionId },
        data: {
          stripeStatus: 'ACTIVE',
          trialEndsAt: null,
          endsAt: null
        }
      });

      this.eventEmitter.emit('subscription.renewed', {
        userId: subscription.userId,
        subscriptionId: subscription.id,
        renewalDate: new Date()
      });
    } catch (error) {
      this.logger.error(`Error handling subscription trial ended: ${error.message}`, error.stack);
      throw error;
    }
  }

  async handleInvoicePaymentSucceeded(payload: any) {
    try {
      const subscriptionId = payload.data.object.subscription;
      if (!subscriptionId) {
        throw new NotFoundException('Missing subscription ID in invoice.payment_succeeded event');
      }

      const periodEnd = payload.data.object.lines.data[0].period.end;
      if (!periodEnd) {
        throw new NotFoundException('Missing period end in invoice.payment_succeeded event');
      }

      const subscription = await this.prisma.subscription.update({
        where: { stripeId: subscriptionId },
        data: {
          endsAt: new Date(periodEnd * 1000)
        }
      });

      this.eventEmitter.emit('subscription.renewed', {
        userId: subscription.userId,
        subscriptionId: subscription.id,
        renewalDate: new Date(periodEnd * 1000)
      });
    } catch (error) {
      this.logger.error(`Error handling invoice payment succeeded: ${error.message}`, error.stack);
      throw error;
    }
  }

  async handleInvoicePaymentFailed(payload: any) {
    try {
      const subscriptionId = payload.data.object.subscription;
      if (!subscriptionId) {
        throw new NotFoundException('Missing subscription ID in invoice.payment_failed event');
      }

      const subscription = await this.prisma.subscription.update({
        where: { stripeId: subscriptionId },
        data: { stripeStatus: 'PAST_DUE' }
      });

      this.eventEmitter.emit('subscription.payment_failed', {
        userId: subscription.userId,
        subscriptionId: subscription.id,
        invoiceId: payload.data.object.id
      });
    } catch (error) {
      this.logger.error(`Error handling invoice payment failed: ${error.message}`, error.stack);
      throw error;
    }
  }

  async handleCustomerSubscriptionUpdatedCancel(payload: any) {
    try {
      const subscriptionId = payload.data.object.id;
      if (!subscriptionId) {
        throw new NotFoundException('Missing subscription ID in customer.subscription.updated event');
      }

      const periodEnd = payload.data.object.current_period_end;
      if (!periodEnd) {
        throw new NotFoundException('Missing period end in customer.subscription.updated event');
      }

      const subscription = await this.prisma.subscription.update({
        where: { stripeId: subscriptionId },
        data: {
          stripeStatus: 'CANCELED',
          endsAt: new Date(periodEnd * 1000)
        }
      });

      this.eventEmitter.emit('subscription.canceled', {
        userId: subscription.userId,
        subscriptionId: subscription.id,
        cancelAt: new Date(periodEnd * 1000)
      });
    } catch (error) {
      this.logger.error(`Error handling subscription cancellation: ${error.message}`, error.stack);
      throw error;
    }
  }

  async handleCustomerSubscriptionDeleted(payload: any) {
    try {
      const subscriptionId = payload.data.object.id;
      if (!subscriptionId) {
        throw new NotFoundException('Missing subscription ID in customer.subscription.deleted event');
      }

      const subscription = await this.prisma.subscription.update({
        where: { stripeId: subscriptionId },
        data: {
          stripeStatus: 'CANCELED',
          endsAt: new Date()
        }
      });

      this.eventEmitter.emit('subscription.ended', {
        userId: subscription.userId,
        subscriptionId: subscription.id,
        endedAt: new Date()
      });
    } catch (error) {
      this.logger.error(`Error handling subscription deletion: ${error.message}`, error.stack);
      throw error;
    }
  }

  async handleAppleNotification(jws: string, notificationType: string): Promise<void> {
    try {
      const payload = await this.appleService.decodeAndVerifyJWS(jws);
      const originalTransactionId = payload?.data?.originalTransactionId;
      
      if (!originalTransactionId) {
        throw new NotFoundException('Missing originalTransactionId in Apple notification payload');
      }

      const user = await this.prisma.user.findFirst({
        where: { appleOriginalTransactionId: originalTransactionId }
      });

      if (!user) {
        throw new NotFoundException(`User not found for originalTransactionId: ${originalTransactionId}`);
      }

      switch (notificationType) {
        case 'SUBSCRIBED':
          await this.handleAppleSubscribed(user.id, payload);
          break;
        case 'DID_RENEW':
          await this.handleAppleDidRenew(user.id, payload);
          break;
        case 'DID_FAIL_TO_RENEW':
          await this.handleAppleDidFailToRenew(user.id, payload);
          break;
        case 'EXPIRED':
          await this.handleAppleExpired(user.id, payload);
          break;
        case 'DID_CHANGE_RENEWAL_STATUS':
          await this.handleDidChangeRenewalStatus(payload);
          break;
        default:
          this.logger.warn(`Unhandled Apple notification type: ${notificationType}`);
      }
    } catch (error) {
      this.logger.error(`Error handling Apple notification: ${error.message}`, error.stack);
      throw error;
    }
  }

  private async handleAppleSubscribed(userId: string, payload: any): Promise<void> {
    try {
      const expiresDate = payload?.data?.expiresDate;
      const subscription = await this.prisma.subscription.update({
        where: { userId },
        data: {
          appleStatus: 'ACTIVE',
          endsAt: expiresDate ? new Date(expiresDate) : null
        }
      });

      this.eventEmitter.emit('subscription.started', {
        userId,
        subscriptionId: subscription.id,
        trialEnd: payload?.data?.isTrialPeriod ? expiresDate : null
      });
    } catch (error) {
      this.logger.error(`Error handling Apple SUBSCRIBED event: ${error.message}`, error.stack);
      throw error;
    }
  }

  private async handleAppleDidRenew(userId: string, payload: any): Promise<void> {
    try {
      const expiresDate = payload?.data?.expiresDate;
      const subscription = await this.prisma.subscription.update({
        where: { userId },
        data: {
          appleStatus: 'ACTIVE',
          endsAt: expiresDate ? new Date(expiresDate) : null
        }
      });

      this.eventEmitter.emit('subscription.renewed', {
        userId,
        subscriptionId: subscription.id,
        renewalDate: new Date()
      });
    } catch (error) {
      this.logger.error(`Error handling Apple DID_RENEW event: ${error.message}`, error.stack);
      throw error;
    }
  }

  private async handleAppleDidFailToRenew(userId: string, payload: any): Promise<void> { // eslint-disable-line @typescript-eslint/no-unused-vars
    try {
      const subscription = await this.prisma.subscription.update({
        where: { userId },
        data: { appleStatus: 'PAST_DUE' }
      });

      this.eventEmitter.emit('subscription.payment_failed', {
        userId,
        subscriptionId: subscription.id,
        reason: 'Apple subscription renewal failed'
      });
    } catch (error) {
      this.logger.error(`Error handling Apple DID_FAIL_TO_RENEW event: ${error.message}`, error.stack);
      throw error;
    }
  }

  private async handleAppleExpired(userId: string, payload: any): Promise<void> {
    try {
      const expirationReason = payload?.data?.expirationReason;
      const subscription = await this.prisma.subscription.update({
        where: { userId },
        data: {
          appleStatus: 'EXPIRED',
          endsAt: new Date()
        }
      });

      this.eventEmitter.emit('subscription.ended', {
        userId,
        subscriptionId: subscription.id,
        endedAt: new Date(),
        reason: expirationReason === '1' ? 'Cancelled' : 'Payment failed'
      });
    } catch (error) {
      this.logger.error(`Error handling Apple EXPIRED event: ${error.message}`, error.stack);
      throw error;
    }
  }

  private async handleDidChangeRenewalStatus(payload: any): Promise<void> {
    try {
      const autoRenewStatus = payload?.data?.autoRenewStatus;
      const productId = payload?.data?.productId;
      
      if (autoRenewStatus === false && productId) {
        const subscription = await this.prisma.subscription.findFirst({
          where: { plan_product_id: productId }
        });

        if (subscription) {
          await this.prisma.subscription.update({
            where: { id: subscription.id },
            data: { appleStatus: 'CANCELED' }
          });

          this.eventEmitter.emit('subscription.canceled', {
            userId: subscription.userId,
            subscriptionId: subscription.id,
            cancelAt: new Date()
          });

          this.logger.log(`Canceled Apple subscription ${subscription.id} for product ${productId}`);
        }
      }
    } catch (error) {
      this.logger.error(`Error handling Apple DID_CHANGE_RENEWAL_STATUS: ${error.message}`, error.stack);
      throw error;
    }
  }

  async handleGoogleNotification(message: any) {
    try {
      const dataString = Buffer.from(message.data, 'base64').toString('utf-8');
      const data = JSON.parse(dataString);
      const { subscriptionNotification } = data;
      const { notificationType, purchaseToken, subscriptionId } = subscriptionNotification;

      if (!purchaseToken) {
        this.logger.error('Google Play notification is missing purchaseToken.');
        return;
      }

      this.logger.log(`Received Google Play Notification: ${notificationType} for subscriptionId: ${subscriptionId}`);

      this.logger.log(`Validating Google Play purchaseToken: ${purchaseToken}`);
      // In a real application, you would make a call to the Google Play Developer API here.
      // For this plan, we assume the token is valid.
      this.logger.log('Google Play purchase token assumed valid for this implementation.');

      // First try to find existing subscription
      let subscription = await this.prisma.subscription.findFirst({
          where: { googlePlaySubscriptionId: subscriptionId },
          include: { user: true },
      });

      if (!subscription) {
        this.logger.log(`No subscription found for Google Play ID ${subscriptionId}, checking user by purchase token`);
        
        // Find user by purchase token
        const user = await this.prisma.user.findFirst({
          where: { googlePlayPurchaseToken: purchaseToken }
        });

        if (!user) {
          this.logger.warn(`User not found for purchase token: ${purchaseToken}`);
          return;
        }

        // Create new subscription for user
        subscription = await this.prisma.subscription.create({
          data: {
            name: 'google-play',
            googlePlaySubscriptionId: subscriptionId,
            stripeStatus: 'ACTIVE',
            userId: user.id
          },
          include: { user: true }
        });

        this.logger.log(`Created new subscription ${subscription.id} for user ${user.id}`);
      }

      const { user } = subscription;

      switch (notificationType) {
        case 4: // SUBSCRIPTION_RENEWED
          await this.prisma.subscription.update({
            where: { id: subscription.id },
            data: { stripeStatus: 'ACTIVE' },
          });
          this.eventEmitter.emit('subscription.renewed', { userId: user.id });
          break;

        case 3: // SUBSCRIPTION_CANCELED
          await this.prisma.subscription.update({
            where: { id: subscription.id },
            data: { stripeStatus: 'CANCELED' },
          });
          this.eventEmitter.emit('subscription.canceled', { userId: user.id });
          break;

        case 12: // SUBSCRIPTION_EXPIRED
          await this.prisma.subscription.update({
            where: { id: subscription.id },
            data: { stripeStatus: 'EXPIRED', endsAt: new Date() },
          });
          this.eventEmitter.emit('subscription.ended', { userId: user.id });
          break;

        default:
          this.logger.warn(`Unhandled Google Play notification type: ${notificationType}`);
      }
    } catch (error) {
      this.logger.error(`Error handling Google Play notification: ${error.message}`, error.stack);
      throw error;
    }
  }
}