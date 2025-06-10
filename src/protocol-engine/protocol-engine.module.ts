import { Module } from '@nestjs/common';
import { BullModule } from '@nestjs/bullmq';
import { ReminderProcessor } from './reminder.processor';
import { ReminderService } from './reminder.service';
import { NotificationService } from './notification.service';
import { PrismaService } from '../common/prisma/prisma.service';
import { ReminderController } from './reminder.controller';

@Module({
  imports: [
    BullModule.registerQueue({
      name: 'reminders',
    }),
  ],
  controllers: [ReminderController],
  providers: [ReminderService, ReminderProcessor, NotificationService, PrismaService],
})
export class ProtocolEngineModule {}