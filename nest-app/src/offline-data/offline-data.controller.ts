import { Controller, Get, Post, Req, UseGuards, Body } from '@nestjs/common';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { OfflineDataService } from './offline-data.service';
import { SyncOfflineDataDto } from './dto/sync-offline-data.dto';
import { ApiBearerAuth, ApiOperation, ApiResponse } from '@nestjs/swagger';

@Controller('offline-data')
@UseGuards(SupabaseAuthGuard)
@ApiBearerAuth()
export class OfflineDataController {
  constructor(private readonly offlineDataService: OfflineDataService) {}

  @Get()
  @ApiOperation({ summary: 'Get all offline data for the current user' })
  @ApiResponse({ status: 200, description: 'Successfully retrieved offline data' })
  @ApiResponse({ status: 401, description: 'Unauthorized' })
  async getData(@Req() req) {
    const userId = req.user.sub;
    const data = await this.offlineDataService.getDataForUser(userId);
    return {
      success: true,
      data
    };
  }

  @Post('sync')
  @ApiOperation({ summary: 'Sync offline data for the current user' })
  @ApiResponse({ status: 200, description: 'Data synced successfully' })
  @ApiResponse({ status: 400, description: 'Invalid request payload' })
  @ApiResponse({ status: 401, description: 'Unauthorized' })
  async syncData(@Req() req, @Body() syncOfflineDataDto: SyncOfflineDataDto) {
    const userId = req.user.sub;
    await this.offlineDataService.syncDataForUser(userId, syncOfflineDataDto.data);
    return {
      success: true,
      message: 'Data synced successfully'
    };
  }
}