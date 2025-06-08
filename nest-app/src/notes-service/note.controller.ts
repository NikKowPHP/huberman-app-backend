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
import { Request } from 'express';

@Controller('notes')
export class NoteController {
    constructor(private readonly noteService: NoteService) {}

    @Post()
    @UseGuards(SupabaseAuthGuard)
    async store(@Req() req: Request, @Body() createNoteDto: CreateNoteDto) {
        const data = {
            ...createNoteDto,
            userId: (req as any).user.sub,
        };
        return this.noteService.createNote(data);
    }

    @Get()
    @UseGuards(SupabaseAuthGuard)
    async index(@Req() req: Request) {
        return this.noteService.getUserNotes((req as any).user.sub);
    }

    @Get(':id')
    @UseGuards(SupabaseAuthGuard)
    async show(@Req() req: Request, @Param('id') id: string) {
        const userId = (req as any).user.sub;
        const note = await this.noteService.getNote(id, userId);
        return { data: note };
    }

    @Put(':id')
    @UseGuards(SupabaseAuthGuard)
    async update(
        @Req() req: Request,
        @Param('id') id: string,
        @Body() updateNoteDto: UpdateNoteDto,
    ) {
        const userId = (req as any).user.sub;
        const updatedNote = await this.noteService.updateNote(id, updateNoteDto, userId);
        return { data: updatedNote };
    }

    @Delete(':id')
    @UseGuards(SupabaseAuthGuard)
    async destroy(@Req() req: Request, @Param('id') id: string) {
        const userId = (req as any).user.sub;
        await this.noteService.deleteNote(id, userId);
        return { data: null };
    }

    @Get('public/:episodeId')
    async listPublicNotesForEpisode(@Param('episodeId') episodeId: string) {
        const notes = await this.noteService.getPublicNotesForEpisode(episodeId);
        return { data: notes };
    }
}
