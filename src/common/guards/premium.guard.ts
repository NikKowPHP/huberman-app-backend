import { Injectable, CanActivate, ExecutionContext } from '@nestjs/common';
import { SubscriptionBillingService } from '../../subscription-billing/subscription-billing.service';

@Injectable()
export class PremiumGuard implements CanActivate {
  constructor(private readonly subscriptionBillingService: SubscriptionBillingService) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest();
    const user = request.user;

    if (!user) {
      return false;
    }

    return this.subscriptionBillingService.userHasActivePremiumSubscription(user.id);
  }
}