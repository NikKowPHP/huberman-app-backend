import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class NoteService {
  constructor(private readonly prisma: PrismaService) {}

  async createNote(data: any) {
    const user = { id: 1 }; // Placeholder for the authenticated user
    data.userId = user.id;
    return this.prisma.note.create({ data });
  }

  async getNote(id: number) {
    return this.prisma.note.findUnique({ where: { id } });
  }

  async updateNote(id: number, data: any) {
    return this.prisma.note.update({ where: { id }, data });
  }

  async deleteNote(id: number) {
    return this.prisma.note.delete({ where: { id } });
  }

  async getUserNotesCount(userId: number) {
    return this.prisma.note.count({ where: { userId } });
  }

  async getPublicNotes() {
    return this.prisma.note.findMany({ where: { isPublic: true } });
  }

  async getUserNotes() {
    const user = { id: 1 }; // Placeholder for the authenticated user
    return this.prisma.note.findMany({ where: { userId: user.id } });
  }

  async getPublicNotesForEpisode(episodeId: number) {
    return this.prisma.note.findMany({
      where: { episodeId, isPublic: true },
    });
  }
}