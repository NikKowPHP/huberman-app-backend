import { Body, Controller, Headers, HttpCode, HttpStatus, Post, Req } from '@nestjs/common';
import { SubscriptionBillingService } from './subscription-billing.service';
import { Request } from 'express';

@Controller('subscription-billing')
export class SubscriptionBillingController {
  constructor(private readonly subscriptionBillingService: SubscriptionBillingService) {}

  @Post('stripe-webhook')
  @HttpCode(HttpStatus.OK)
  async handleStripeWebhook(@Req() req: Request, @Headers('stripe-signature') signature: string) {
    const event = this.subscriptionBillingService.verifyStripeWebhook(req.rawBody, signature);
    await this.subscriptionBillingService.handleStripeEvent(event);
    return { received: true };
  }

  @Post('apple-webhook')
  @HttpCode(HttpStatus.OK)
  async handleAppleWebhook(@Body() body: any, @Headers('x-apple-notification-type') notificationType: string) {
    const jws = body.signedPayload;
    await this.subscriptionBillingService.handleAppleNotification(jws, notificationType);
    return { received: true };
  }
}