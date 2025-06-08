import { Controller, Get, Param, Req } from '@nestjs/common';
import { ContentService } from './content.service';
import { ProtocolResource } from './protocol.resource';
import { Request } from 'express';
import { SubscriptionBillingService } from '../subscription-billing/subscription-billing.service';

@Controller('protocols')
export class ProtocolController {
  constructor(
    private readonly contentService: ContentService,
    private readonly subscriptionService: SubscriptionBillingService,
  ) {}

  @Get()
  async index(@Req() request: Request): Promise<any> {
    const user = request['user'];
    let protocols;

    if (user && await this.subscriptionService.userHasActivePremiumSubscription(user)) {
      protocols = await this.contentService.getProtocols();
    } else {
      protocols = await this.contentService.getProtocols({ where: { is_free: true } });
    }

    return ProtocolResource.collection(protocols);
  }

  @Get(':id')
  async show(@Param('id') id: string): Promise<any> {
    const protocol = await this.contentService.getProtocolDetails(parseInt(id));
    return new ProtocolResource(protocol);
  }
}