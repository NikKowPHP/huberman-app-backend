import {
    Controller,
    Post,
    Body,
    Get,
    Param,
    Put,
    Delete,
    UseGuards,
    Req,
} from '@nestjs/common';
import { NoteService } from './note.service';
import { CreateNoteDto } from './dto/create-note.dto';
import { UpdateNoteDto } from './dto/update-note.dto';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { NoteOwnerGuard } from './guards/note-owner.guard';
import { Request } from 'express';

@Controller('notes')
export class NoteController {
    constructor(private readonly noteService: NoteService) {}

    @Post()
    @UseGuards(SupabaseAuthGuard)
    async store(@Req() req: Request, @Body() createNoteDto: CreateNoteDto) {
        const userId = (req as any).user.sub;
        return this.noteService.createNote(createNoteDto, userId);
    }

    @Get()
    @UseGuards(SupabaseAuthGuard)
    async index(@Req() req: Request) {
        return this.noteService.getUserNotes((req as any).user.sub);
    }

    @Get(':id')
    @UseGuards(SupabaseAuthGuard, NoteOwnerGuard)
    async show(@Param('id') id: string) {
        const note = await this.noteService.getNote(id);
        return { data: note };
    }

    @Put(':id')
    @UseGuards(SupabaseAuthGuard, NoteOwnerGuard)
    async update(
        @Param('id') id: string,
        @Body() updateNoteDto: UpdateNoteDto,
    ) {
        return this.noteService.updateNote(id, updateNoteDto);
    }

    @Delete(':id')
    @UseGuards(SupabaseAuthGuard)
    async destroy(@Param('id') id: string) {
        await this.noteService.deleteNote(id);
        return { data: null };
    }

    @Get('public/:episodeId')
    async listPublicNotesForEpisode(@Param('episodeId') episodeId: string) {
        const notes = await this.noteService.getPublicNotesForEpisode(episodeId);
        return { data: notes };
    }
}
