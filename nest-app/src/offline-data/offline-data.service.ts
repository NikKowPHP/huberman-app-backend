import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class OfflineDataService {
  constructor(private readonly prisma: PrismaService) {}

  async getDataForUser(userId: string) {
    return this.prisma.offlineData.findMany({
      where: { userId }
    });
  }

  async syncDataForUser(userId: string, data: Array<{key: string, value: any}>) {
    await Promise.all(data.map(item => 
      this.prisma.offlineData.upsert({
        where: {
          userId_key: {
            userId,
            key: item.key
          }
        },
        update: {
          value: JSON.stringify(item.value)
        },
        create: {
          userId,
          key: item.key,
          value: JSON.stringify(item.value)
        }
      })
    ));
  }
}