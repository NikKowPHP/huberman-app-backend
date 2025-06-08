import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class NotificationService {
    constructor(private readonly prisma: PrismaService) {}

    async sendReminderNotification(reminderId: number) {
        const reminder = await this.prisma.userReminder.findUnique({
            where: { id: reminderId },
            include: {
                user: {
                    include: {
                        devices: true
                    }
                },
                protocol: true
            }
        });

        if (!reminder) {
            console.error(`Reminder with ID ${reminderId} not found`);
            return;
        }

        const { user, protocol, message } = reminder;
        const title = `Reminder: ${protocol.title}`;
        const body = message || `Time to follow your protocol: ${protocol.title}`;

        // Send notifications to all user devices
        for (const device of user.devices) {
            try {
                if (device.platform === 'IOS') {
                    await this.sendApnsNotification(device.deviceToken, title, body);
                } else if (device.platform === 'ANDROID') {
                    await this.sendFcmNotification(device.deviceToken, title, body);
                }
            } catch (error) {
                console.error(`Error sending notification to device ${device.id}:`, error);
            }
        }
    }

    private async sendFcmNotification(token: string, title: string, body: string) {
        // Placeholder for FCM notification logic
        console.log(`Sending FCM to ${token}: ${title} - ${body}`);
        // Actual implementation would use firebase-admin SDK
        // await admin.messaging().send({
        //   token: token,
        //   notification: { title, body }
        // });
    }

    private async sendApnsNotification(token: string, title: string, body: string) {
        // Placeholder for APNS notification logic
        console.log(`Sending APNS to ${token}: ${title} - ${body}`);
        // Actual implementation would use @nestjs/apn or similar
        // await apnProvider.send({
        //   token: token,
        //   notification: { title, body, sound: 'default' }
        // });
    }
}