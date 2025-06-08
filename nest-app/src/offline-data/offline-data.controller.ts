import { Controller, Get, Post, Req, UseGuards, Body } from '@nestjs/common';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { OfflineDataService } from './offline-data.service';
import { SyncOfflineDataDto } from './dto/sync-offline-data.dto';

@Controller('offline-data')
@UseGuards(SupabaseAuthGuard)
export class OfflineDataController {
  constructor(private readonly offlineDataService: OfflineDataService) {}

  @Get()
  async getData(@Req() req) {
    const userId = req.user.sub;
    const data = await this.offlineDataService.getDataForUser(userId);
    return {
      success: true,
      data
    };
  }

  @Post('sync')
  async syncData(@Req() req, @Body() syncOfflineDataDto: SyncOfflineDataDto) {
    const userId = req.user.sub;
    await this.offlineDataService.syncDataForUser(userId, syncOfflineDataDto.data);
    return {
      success: true,
      message: 'Data synced successfully'
    };
  }
}