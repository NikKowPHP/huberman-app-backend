import { Controller, Post, Get, Body, Req, Param, UseGuards } from '@nestjs/common';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { TrackingService } from './tracking.service';

@Controller('tracking')
@UseGuards(SupabaseAuthGuard)
export class TrackingController {
  constructor(private readonly trackingService: TrackingService) {}

  @Post('/log')
  async store(@Req() req, @Body() storeTrackingLogDto: any) {
    throw new Error('Not implemented');
  }

  @Get('/summary/:protocolId')
  async getSummary(@Req() req, @Param('protocolId') protocolId: string) {
    throw new Error('Not implemented');
  }
}