import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';
import { StoreReminderDto } from './dto/store-reminder.dto';
import { UpdateReminderDto } from './dto/update-reminder.dto';

@Injectable()
export class ReminderService {
    constructor(private readonly prisma: PrismaService) {}

    async setReminder(user: any, storeReminderDto: StoreReminderDto) {
        return this.prisma.userReminder.create({
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
    }

    async getUserReminders(user: any) {
        return this.prisma.userReminder.findMany({
            where: { userId: user.id },
        });
    }

    async getReminder(id: number) {
        return this.prisma.userReminder.findUnique({ where: { id } });
    }

    async updateReminder(id: number, updateReminderDto: UpdateReminderDto) {
        return this.prisma.userReminder.update({
            where: { id },
            data: updateReminderDto,
        });
    }

    async deleteReminder(id: number) {
        return this.prisma.userReminder.delete({ where: { id } });
    }
}
