import { Body, Controller, Get, Headers, HttpCode, HttpStatus, Post, Req, UseGuards } from '@nestjs/common';
import { SubscriptionBillingService } from './subscription-billing.service';
import { Request } from 'express';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';

@Controller('subscription-billing')
export class SubscriptionBillingController {
  constructor(private readonly subscriptionBillingService: SubscriptionBillingService) {}

  @Get('plans')
  @UseGuards(SupabaseAuthGuard)
  async getPlans() {
    return this.subscriptionBillingService.getPlans();
  }

  @Get('user-subscription')
  @UseGuards(SupabaseAuthGuard)
  async getUserSubscription(@Req() req: Request) {
    return this.subscriptionBillingService.getUserSubscription(req['user'].sub);
  }

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

  @Post('google-webhook')
  @HttpCode(HttpStatus.OK)
  async handleGoogleWebhook(@Body() body: any) {
    const message = body.message;
    await this.subscriptionBillingService.handleGoogleNotification(message);
    return { received: true };
  }
}