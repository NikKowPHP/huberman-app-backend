import { Processor, Process } from '@nestjs/bullmq';
import { Job } from 'bullmq';
import { PrismaService } from '../common/prisma/prisma.service';

@Processor('reminders')
export class ReminderProcessor {
  constructor(private prisma: PrismaService) {}

  @Process('send-protocol-reminder')
  async handleSendReminder(job: Job<{ reminderId: string }>) {
    const { reminderId } = job.data;
    
    const reminder = await this.prisma.userReminder.findUnique({
      where: { id: reminderId },
      include: { user: true }
    });

    if (!reminder) {
      throw new Error(`Reminder with ID ${reminderId} not found`);
    }

    // TODO: Implement notification sending (will be added in notification task)
    // Placeholder: Notification.send(reminder.user, new ProtocolReminder());

    await this.prisma.userReminder.update({
      where: { id: reminderId },
      data: { last_sent_at: new Date() }
    });
  }
}