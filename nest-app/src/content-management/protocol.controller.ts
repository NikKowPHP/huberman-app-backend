import { Controller, Get, Param, Req } from '@nestjs/common';
import { ContentService } from './content.service';
import { ProtocolResource } from './protocol.resource';
import { Request } from 'express';
import { SubscriptionBillingService } from '../subscription-billing/subscription-billing.service';
import { ApiOperation, ApiResponse, ApiBearerAuth, ApiParam } from '@nestjs/swagger';

@Controller('protocols')
export class ProtocolController {
  constructor(
    private readonly contentService: ContentService,
    private readonly subscriptionService: SubscriptionBillingService,
  ) {}

  @Get()
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get a list of protocols' })
  @ApiResponse({
    status: 200,
    description: 'List of protocols (content may vary based on subscription status)',
    type: [ProtocolResource]
  })
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
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get protocol details' })
  @ApiParam({ name: 'id', type: 'string', description: 'ID of the protocol' })
  @ApiResponse({
    status: 200,
    description: 'Protocol details',
    type: ProtocolResource
  })
  @ApiResponse({
    status: 404,
    description: 'Protocol not found'
  })
  async show(@Param('id') id: string): Promise<any> {
    const protocol = await this.contentService.getProtocolDetails(parseInt(id));
    return new ProtocolResource(protocol);
  }
}