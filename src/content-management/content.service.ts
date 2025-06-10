import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class ContentService {
  constructor(private readonly prisma: PrismaService) {}

  async getProtocols(options?: { where?: { is_free?: boolean } }) {
    return this.prisma.protocol.findMany(options);
  }

  async getProtocolDetails(id: number) {
    return this.prisma.protocol.findUnique({ where: { id } });
  }

  async getEpisodes() {
    return this.prisma.episode.findMany();
  }

  async getEpisodeDetails(id: number) {
    return this.prisma.episode.findUnique({ where: { id } });
  }

  async getSummariesForEpisode(episodeId: number) {
    return this.prisma.summary.findMany({ where: { episodeId } });
  }
}