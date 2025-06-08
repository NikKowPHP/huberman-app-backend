import { Controller, Post, Req, Res, RawBodyRequest } from '@nestjs/common';
import { Request, Response } from 'express';
import { SubscriptionBillingService } from './subscription-billing.service';

@Controller('webhook')
export class SubscriptionBillingController {
  constructor(private readonly subscriptionBillingService: SubscriptionBillingService) {}

  @Post('stripe')
  async handleStripeWebhook(@Req() req: RawBodyRequest<Request>, @Res() res: Response) {
    const sig = Array.isArray(req.headers['stripe-signature'])
      ? req.headers['stripe-signature'][0]
      : req.headers['stripe-signature'];
    
    if (!sig) {
      return res.status(400).send('Missing stripe-signature header');
    }
    
    try {
      // Verify event using Stripe SDK
      const event = await this.subscriptionBillingService.verifyStripeWebhook(req.rawBody, sig);
      
      // Route to appropriate handler based on event type
      switch (event.type) {
        case 'checkout.session.completed':
          await this.subscriptionBillingService.handleCheckoutSessionCompleted(event);
          break;
        case 'invoice.payment_succeeded':
          await this.subscriptionBillingService.handleInvoicePaymentSucceeded(event);
          break;
        case 'invoice.payment_failed':
          await this.subscriptionBillingService.handleInvoicePaymentFailed(event);
          break;
        case 'customer.subscription.updated':
          // Handle both trial ended and cancel events
          if (event.data.object.cancel_at_period_end) {
            await this.subscriptionBillingService.handleCustomerSubscriptionUpdatedCancel(event);
          } else {
            await this.subscriptionBillingService.handleCustomerSubscriptionUpdatedTrialEnded(event);
          }
          break;
        case 'customer.subscription.deleted':
          await this.subscriptionBillingService.handleCustomerSubscriptionDeleted(event);
          break;
        default:
          console.log(`Unhandled event type: ${event.type}`);
      }
      
      res.status(200).send();
    } catch (err) {
      console.error(`Stripe webhook error: ${err.message}`);
      res.status(400).send(`Webhook Error: ${err.message}`);
    }
  }
}