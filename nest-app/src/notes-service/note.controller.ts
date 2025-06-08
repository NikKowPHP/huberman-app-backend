import { Controller, Post, Body, Get, Param, Put, Delete, UseGuards } from '@nestjs/common';
import { NoteService } from './note.service';
import { CreateNoteDto } from './dto/create-note.dto';
import { UpdateNoteDto } from './dto/update-note.dto';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';

@Controller('notes')
export class NoteController {
  constructor(private readonly noteService: NoteService) {}

  @Post()
  @UseGuards(SupabaseAuthGuard)
  async store(@Body() createNoteDto: CreateNoteDto) {
    return this.noteService.createNote(createNoteDto);
  }

  @Get()
  @UseGuards(SupabaseAuthGuard)
  async index() {
    return this.noteService.getUserNotes();
  }

  @Get(':id')
  @UseGuards(SupabaseAuthGuard)
  async show(@Param('id') id: number) {
    return this.noteService.getNote(id);
  }

  @Put(':id')
  @UseGuards(SupabaseAuthGuard)
  async update(@Param('id') id: number, @Body() updateNoteDto: UpdateNoteDto) {
    return this.noteService.updateNote(id, updateNoteDto);
  }

  @Delete(':id')
  @UseGuards(SupabaseAuthGuard)
  async destroy(@Param('id') id: number) {
    return this.noteService.deleteNote(id);
  }

  @Get('public/:episodeId')
  async listPublicNotesForEpisode(@Param('episodeId') episodeId: number) {
    return this.noteService.getPublicNotesForEpisode(episodeId);
  }
}