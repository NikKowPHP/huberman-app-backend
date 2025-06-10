import { Processor } from '@nestjs/bullmq';
import { Job } from 'bullmq';
import { PrismaService } from '../common/prisma/prisma.service';
import { NotificationService } from './notification.service';

@Processor('reminders')
export class ReminderProcessor {
  constructor(
    private prisma: PrismaService,
    private notificationService: NotificationService
  ) {}

  async process(job: Job<{ reminderId: string }>) {
    const { reminderId } = job.data;
    
    const reminder = await this.prisma.userReminder.findUnique({
      where: { id: reminderId },
      include: { user: true }
    });

    if (!reminder) {
      throw new Error(`Reminder with ID ${reminderId} not found`);
    }

    // Send the reminder notification
    await this.notificationService.sendReminderNotification(Number(reminderId));

    await this.prisma.userReminder.update({
      where: { id: reminderId },
      data: { last_sent_at: new Date() }
    });
  }
}