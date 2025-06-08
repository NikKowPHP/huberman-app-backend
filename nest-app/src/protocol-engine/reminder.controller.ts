import { Controller, Post, Body, Get, Param, Put, Delete, UseGuards } from '@nestjs/common';
import { ReminderService } from './reminder.service';
import { StoreReminderDto } from './dto/store-reminder.dto';
import { UpdateReminderDto } from './dto/update-reminder.dto';
import { SupabaseAuthGuard } from '../common/guards/supabase-auth.guard';

@Controller('reminders')
export class ReminderController {
  constructor(private readonly reminderService: ReminderService) {}

  @Post()
  @UseGuards(SupabaseAuthGuard)
  async store(@Body() storeReminderDto: StoreReminderDto) {
    return this.reminderService.setReminder(storeReminderDto);
  }

  @Get()
  @UseGuards(SupabaseAuthGuard)
  async index() {
    return this.reminderService.getUserReminders();
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