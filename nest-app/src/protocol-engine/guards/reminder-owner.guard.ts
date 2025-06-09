import { CanActivate, ExecutionContext, Injectable, ForbiddenException } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class ReminderOwnerGuard implements CanActivate {
    constructor(private readonly prisma: PrismaService) {}

    async canActivate(context: ExecutionContext): Promise<boolean> {
        const request = context.switchToHttp().getRequest();
        const userId = request.user?.sub;
        const reminderId = parseInt(request.params.id);

        const reminder = await this.prisma.userReminder.findUnique({
            where: { id: reminderId },
            select: { userId: true }
        });

        if (!reminder || reminder.userId !== userId) {
            throw new ForbiddenException('You do not have permission to access this reminder');
        }

        return true;
    }
}