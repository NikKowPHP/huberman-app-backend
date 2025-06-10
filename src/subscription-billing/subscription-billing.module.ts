import { Module } from '@nestjs/common';
import { SubscriptionBillingService } from './subscription-billing.service';
import { SubscriptionBillingController } from './subscription-billing.controller';
import { AppleService } from './apple.service';
import { EventEmitterModule } from '@nestjs/event-emitter';
import { SubscriptionRenewedListener } from './listeners/subscription-renewed.listener';
import { PrismaService } from '../common/prisma/prisma.service';

@Module({
  imports: [EventEmitterModule.forRoot()],
  controllers: [SubscriptionBillingController],
  providers: [
    SubscriptionBillingService,
    AppleService,
    SubscriptionRenewedListener,
    PrismaService
  ]
})
export class SubscriptionBillingModule {}