import { Module } from '@nestjs/common';
import { NoteController } from './note.controller';
import { NoteService } from './note.service';
import { PrismaService } from '../common/prisma/prisma.service';
import { NoteOwnerGuard } from './guards/note-owner.guard';

@Module({
  controllers: [NoteController],
  providers: [NoteService, PrismaService, NoteOwnerGuard],
})
export class NotesServiceModule {}