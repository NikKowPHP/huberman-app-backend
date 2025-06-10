import { Controller, Post, Get, Body, Req, Param, UseGuards } from '@nestjs/common';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { TrackingService } from './tracking.service';
import { StoreTrackingLogDto } from './dto/store-tracking-log.dto';
import { ApiOperation, ApiResponse, ApiBearerAuth, ApiParam } from '@nestjs/swagger';

@Controller('tracking')
@UseGuards(SupabaseAuthGuard)
@ApiBearerAuth()
export class TrackingController {
  constructor(private readonly trackingService: TrackingService) {}

  @Post('/log')
  @ApiOperation({ summary: 'Log a tracking entry for a protocol' })
  @ApiResponse({
    status: 201,
    description: 'Tracking entry successfully created'
  })
  async store(@Req() req, @Body() storeTrackingLogDto: StoreTrackingLogDto) {
    return this.trackingService.logAdherence(
      req.user,
      storeTrackingLogDto.protocol_id,
      storeTrackingLogDto.tracked_at,
      storeTrackingLogDto.notes,
      storeTrackingLogDto.metadata
    );
  }

  @Get('/summary/:protocolId')
  @ApiOperation({ summary: 'Get tracking summary for a protocol' })
  @ApiParam({
    name: 'protocolId',
    description: 'ID of the protocol to get summary for'
  })
  @ApiResponse({
    status: 200,
    description: 'Tracking summary data'
  })
  async getSummary(@Req() req, @Param('protocolId') protocolId: string) {
    return this.trackingService.calculateStreak(
      req.user,
      parseInt(protocolId, 10)
    );
  }
}