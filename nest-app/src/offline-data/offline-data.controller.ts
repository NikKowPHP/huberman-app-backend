import { Controller, Get, Post, Req, UseGuards } from '@nestjs/common';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { OfflineDataService } from './offline-data.service';

@Controller('offline-data')
@UseGuards(SupabaseAuthGuard)
export class OfflineDataController {
  constructor(private readonly offlineDataService: OfflineDataService) {}

  @Get()
  async getData(@Req() req) {
    const userId = req.user.sub;
    console.log(`Getting offline data for user ${userId}`);
    return this.offlineDataService.getDataForUser(userId);
  }

  @Post('sync')
  async syncData(@Req() req) {
    const userId = req.user.sub;
    const data = req.body;
    console.log(`Syncing offline data for user ${userId}`);
    return this.offlineDataService.syncDataForUser(userId, data);
  }
}