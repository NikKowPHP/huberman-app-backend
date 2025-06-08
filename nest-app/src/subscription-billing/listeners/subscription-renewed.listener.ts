import { Injectable, Logger } from '@nestjs/common';
import { OnEvent } from '@nestjs/event-emitter';
import { SubscriptionRenewedEvent } from '../../common/events/subscription-renewed.event';
import { PrismaService } from '../../common/prisma/prisma.service';

@Injectable()
export class SubscriptionRenewedListener {
  private readonly logger = new Logger(SubscriptionRenewedListener.name);

  constructor(private readonly prisma: PrismaService) {}

  @OnEvent(SubscriptionRenewedEvent.eventName)
  async handleSubscriptionRenewedEvent(event: SubscriptionRenewedEvent) {
    const { userId } = event;
    this.logger.log(`Clearing entitlement cache for user ${userId}`);
    
    // In a real implementation, we would clear cache here
    // For now, we'll log and update user's premium status
    try {
      await this.prisma.user.update({
        where: { id: userId },
        data: { isPremium: true }
      });
      this.logger.log(`Updated premium status for user ${userId}`);
    } catch (error) {
      this.logger.error(`Failed to update user ${userId}: ${error.message}`);
    }
  }
}