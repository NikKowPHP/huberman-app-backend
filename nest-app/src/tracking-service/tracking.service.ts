import { Injectable, NotImplementedException } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class TrackingService {
  constructor(private readonly prisma: PrismaService) {}

  async logAdherence(user: any, protocolId: number, date: string, notes?: string, metadata?: object) {
    throw new NotImplementedException('logAdherence not implemented');
  }

  async getUserTrackingData(user: any, protocolId: number, dateRange: { start: string; end: string }) {
    throw new NotImplementedException('getUserTrackingData not implemented');
  }

  async calculateStreak(user: any, protocolId: number) {
    throw new NotImplementedException('calculateStreak not implemented');
  }

  async getTrackingLogById(user: any, logId: number) {
    throw new NotImplementedException('getTrackingLogById not implemented');
  }

  async updateTrackingLog(user: any, logId: number, data: any) {
    throw new NotImplementedException('updateTrackingLog not implemented');
  }

  async deleteTrackingLog(user: any, logId: number) {
    throw new NotImplementedException('deleteTrackingLog not implemented');
  }
}