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
    NotFoundException,
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
        const note = await this.noteService.getNote(id);
        if (!note || note.userId !== (req as any).user.sub) {
            throw new NotFoundException('Note not found');
        }
        return { data: note };
    }

    @Put(':id')
    @UseGuards(SupabaseAuthGuard)
    async update(
        @Req() req: Request,
        @Param('id') id: string,
        @Body() updateNoteDto: UpdateNoteDto,
    ) {
        const note = await this.noteService.getNote(id);
        if (!note || note.userId !== (req as any).user.sub) {
            throw new NotFoundException('Note not found');
        }
        const updatedNote = await this.noteService.updateNote(id, updateNoteDto);
        return { data: updatedNote };
    }

    @Delete(':id')
    @UseGuards(SupabaseAuthGuard)
    async destroy(@Req() req: Request, @Param('id') id: string) {
        const note = await this.noteService.getNote(id);
        if (!note || note.userId !== (req as any).user.sub) {
            throw new NotFoundException('Note not found');
        }
        await this.noteService.deleteNote(id);
        return { data: null };
    }

    @Get('public/:episodeId')
    async listPublicNotesForEpisode(@Param('episodeId') episodeId: string) {
        const notes = await this.noteService.getPublicNotesForEpisode(episodeId);
        return { data: notes };
    }
}
