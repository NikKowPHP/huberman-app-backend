import { Injectable, Logger } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';
import * as admin from 'firebase-admin';

@Injectable()
export class NotificationService {
    private readonly logger = new Logger(NotificationService.name);
    
    constructor(private readonly prisma: PrismaService) {
        // Initialize Firebase Admin SDK
        if (!admin.apps.length) {
            admin.initializeApp({
                credential: admin.credential.cert({
                    projectId: process.env.FIREBASE_PROJECT_ID,
                    clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
                    privateKey: process.env.FIREBASE_PRIVATE_KEY?.replace(/\\n/g, '\n')
                })
            });
        }
    }

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
        try {
            await admin.messaging().send({
                token: token,
                notification: { title, body },
                android: {
                    priority: 'high'
                }
            });
            this.logger.log(`FCM notification sent to ${token}`);
        } catch (error) {
            this.logger.error(`Failed to send FCM to ${token}:`, error);
            throw error;
        }
    }

    private async sendApnsNotification(token: string, title: string, body: string) {
        try {
            await admin.messaging().send({
                token: token,
                notification: { title, body },
                apns: {
                    payload: {
                        aps: {
                            sound: 'default'
                        }
                    }
                }
            });
            this.logger.log(`APNS notification sent to ${token}`);
        } catch (error) {
            this.logger.error(`Failed to send APNS to ${token}:`, error);
            throw error;
        }
    }
}