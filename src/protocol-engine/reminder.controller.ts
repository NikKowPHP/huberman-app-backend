import { Controller, Post, Body, Get, Param, Put, Delete, UseGuards, Req } from '@nestjs/common';
import { Request } from 'express';
import { ReminderService } from './reminder.service';
import { StoreReminderDto } from './dto/store-reminder.dto';
import { UpdateReminderDto } from './dto/update-reminder.dto';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';

@Controller('reminders')
export class ReminderController {
  constructor(private readonly reminderService: ReminderService) {}

  @Post()
  @UseGuards(SupabaseAuthGuard)
  async store(@Body() storeReminderDto: StoreReminderDto, @Req() request: Request) {
      return this.reminderService.setReminder(storeReminderDto, { id: request['user'].sub });
  }

  @Get()
  @UseGuards(SupabaseAuthGuard)
  async index(@Req() request: Request) {
      return this.reminderService.getUserReminders({ id: request['user'].sub });
  }

  @Get(':id')
  @UseGuards(SupabaseAuthGuard)
  async show(@Param('id') id: number) {
    return this.reminderService.getReminder(id);
  }

  @Put(':id')
  @UseGuards(SupabaseAuthGuard)
  async update(@Param('id') id: number, @Body() updateReminderDto: UpdateReminderDto) {
    return this.reminderService.updateReminder(id, updateReminderDto);
  }

  @Delete(':id')
  @UseGuards(SupabaseAuthGuard)
  async destroy(@Param('id') id: number) {
    return this.reminderService.deleteReminder(id);
  }
}