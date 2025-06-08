import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class TrackingService {
  constructor(private readonly prisma: PrismaService) {}

  async logAdherence(user: { id: string }, protocolId: number, date: string, notes?: string, metadata?: object) {
    return this.prisma.trackingLog.upsert({
      where: {
        userId_trackedAt_protocolId: {
          userId: user.id,
          trackedAt: new Date(date),
          protocolId
        }
      },
      update: {
        notes,
        metadata: metadata ? JSON.stringify(metadata) : undefined
      },
      create: {
        userId: user.id,
        protocolId,
        trackedAt: new Date(date),
        notes,
        metadata: metadata ? JSON.stringify(metadata) : undefined
      }
    });
  }

  async getUserTrackingData(user: { id: string }, protocolId?: number, dateRange?: { start: string; end: string }) {
    const where: any = { userId: user.id };
    
    if (protocolId) {
      where.protocolId = protocolId;
    }

    if (dateRange) {
      where.trackedAt = {
        gte: new Date(dateRange.start),
        lte: new Date(dateRange.end)
      };
    }

    return this.prisma.trackingLog.findMany({
      where,
      orderBy: { trackedAt: 'desc' }
    });
  }

  async calculateStreak(user: { id: string }, protocolId: number): Promise<number> {
    const logs = await this.prisma.trackingLog.findMany({
      where: { userId: user.id, protocolId },
      orderBy: { trackedAt: 'desc' },
      select: { trackedAt: true }
    });

    if (logs.length === 0) return 0;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    const latestLogDate = new Date(logs[0].trackedAt);
    latestLogDate.setHours(0, 0, 0, 0);

    if (latestLogDate.getTime() !== today.getTime() && 
        latestLogDate.getTime() !== yesterday.getTime()) {
      return 0;
    }

    let streak = 0;
    let expectedDate = latestLogDate.getTime() === today.getTime() ? today : yesterday;

    for (const log of logs) {
      const logDate = new Date(log.trackedAt);
      logDate.setHours(0, 0, 0, 0);

      if (logDate.getTime() === expectedDate.getTime()) {
        streak++;
        expectedDate = new Date(expectedDate);
        expectedDate.setDate(expectedDate.getDate() - 1);
      } else if (logDate.getTime() < expectedDate.getTime()) {
        break;
      }
    }

    return streak;
  }

  async getTrackingLogById(user: { id: string }, logId: number) {
    return this.prisma.trackingLog.findFirst({
      where: { id: logId, userId: user.id }
    });
  }

  async updateTrackingLog(user: { id: string }, logId: number, data: any) {
    const log = await this.getTrackingLogById(user, logId);
    if (!log) return null;

    const { trackedAt, ...updateData } = data;
    if (trackedAt) updateData.trackedAt = new Date(trackedAt);
    if (updateData.metadata) updateData.metadata = JSON.stringify(updateData.metadata);

    return this.prisma.trackingLog.update({
      where: { id: logId },
      data: updateData
    });
  }

  async deleteTrackingLog(user: { id: string }, logId: number): Promise<boolean> {
    const log = await this.getTrackingLogById(user, logId);
    if (!log) return false;

    await this.prisma.trackingLog.delete({ where: { id: logId } });
    return true;
  }
}