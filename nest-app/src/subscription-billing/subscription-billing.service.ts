import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class SubscriptionBillingService {
  constructor(private readonly prisma: PrismaService) {}

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
}