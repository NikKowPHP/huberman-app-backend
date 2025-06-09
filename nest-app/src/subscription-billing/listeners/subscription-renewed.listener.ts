import { Injectable, Logger } from '@nestjs/common';
import { OnEvent } from '@nestjs/event-emitter';
import { SubscriptionRenewedEvent } from '../../common/events/subscription-renewed.event';

@Injectable()
export class SubscriptionRenewedListener {
  private readonly logger = new Logger(SubscriptionRenewedListener.name);

  constructor() {}

  @OnEvent(SubscriptionRenewedEvent.eventName)
  async handleSubscriptionRenewedEvent(event: SubscriptionRenewedEvent) {
    const { userId } = event;
    
    this.logger.log(`[Cache] Clearing premium entitlements for user ${userId}`);

    // TODO: Implement actual cache clearing once CacheModule is configured
    // Example with @nestjs/cache-manager:
    // await this.cacheManager.del(`user:${userId}:premium_entitlements`);
  }
}