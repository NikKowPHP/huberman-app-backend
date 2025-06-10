// nest-app/src/common/events/subscription-renewed.event.ts
export class SubscriptionRenewedEvent {
  static eventName = 'subscription.renewed';
  
  constructor(public userId: string) {}
}