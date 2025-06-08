import { Module } from '@nestjs/common';
import { TrackingController } from './tracking.controller';
import { TrackingService } from './tracking.service';
import { PrismaService } from '../common/prisma/prisma.service';

@Module({
  controllers: [TrackingController],
  providers: [TrackingService, PrismaService],
})
export class TrackingServiceModule {}