import { Injectable, CanActivate, ExecutionContext, ForbiddenException } from '@nestjs/common';
import { PrismaService } from '../../common/prisma/prisma.service';

@Injectable()
export class NoteOwnerGuard implements CanActivate {
    constructor(private readonly prisma: PrismaService) {}

    async canActivate(context: ExecutionContext): Promise<boolean> {
        const request = context.switchToHttp().getRequest();
        const noteId = request.params.id;
        const userId = request.user?.sub;

        const note = await this.prisma.note.findUnique({
            where: { id: noteId },
        });

        if (!note || note.userId !== userId) {
            throw new ForbiddenException('Note not found or access denied');
        }

        return true;
    }
}