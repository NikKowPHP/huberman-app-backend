import { Module } from '@nestjs/common';
import { OfflineDataController } from './offline-data.controller';
import { OfflineDataService } from './offline-data.service';
import { PrismaService } from '../common/prisma/prisma.service';

@Module({
  controllers: [OfflineDataController],
  providers: [OfflineDataService, PrismaService],
})
export class OfflineDataModule {}