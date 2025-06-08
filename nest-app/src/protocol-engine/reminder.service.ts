import { Injectable } from '@nestjs/common';
import { InjectQueue } from '@nestjs/bullmq';
import { Queue } from 'bullmq';
import { PrismaService } from '../common/prisma/prisma.service';
import { StoreReminderDto } from './dto/store-reminder.dto';
import { UpdateReminderDto } from './dto/update-reminder.dto';

@Injectable()
export class ReminderService {
    constructor(
        private readonly prisma: PrismaService,
        @InjectQueue('reminders') private readonly reminderQueue: Queue
    ) {}

    async setReminder(storeReminderDto: StoreReminderDto, user: { id: string }) {
        const reminder = await this.prisma.userReminder.create({
            data: {
                userId: user.id,
                reminderTime: storeReminderDto.reminderTime,
                frequency: storeReminderDto.frequency,
                specificDays: storeReminderDto.specificDays,
                message: storeReminderDto.message,
                isActive: storeReminderDto.isActive,
                protocolId: storeReminderDto.protocolId,
            },
        });

        // Add job to queue for the new reminder
        if (reminder.isActive) {
            await this.reminderQueue.add('send-reminder', {
                reminderId: reminder.id,
            });
        }

        return reminder;
    }

    async getUserReminders(user: { id: string }) {
        return this.prisma.userReminder.findMany({
            where: { userId: user.id },
        });
    }

    async getReminder(id: number) {
        return this.prisma.userReminder.findUnique({ where: { id } });
    }

    async updateReminder(id: number, updateReminderDto: UpdateReminderDto) {
        const reminder = await this.prisma.userReminder.update({
            where: { id },
            data: updateReminderDto,
        });

        // Update job in queue if reminder is active
        if (reminder.isActive) {
            await this.reminderQueue.add('send-reminder', {
                reminderId: reminder.id,
            });
        }

        return reminder;
    }

    async deleteReminder(id: number) {
        return this.prisma.userReminder.delete({ where: { id } });
    }
}
