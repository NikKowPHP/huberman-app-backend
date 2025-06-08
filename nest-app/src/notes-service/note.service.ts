import { Injectable, ForbiddenException } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';
import { SubscriptionBillingService } from '../subscription-billing/subscription-billing.service';
import { CreateNoteDto } from './dto/create-note.dto';
import { UpdateNoteDto } from './dto/update-note.dto';

@Injectable()
export class NoteService {
    private readonly MAX_FREE_NOTES = 5;

    constructor(
        private readonly prisma: PrismaService,
        private readonly subscriptionBillingService: SubscriptionBillingService
    ) {}

    async createNote(createNoteDto: CreateNoteDto, userId: string) {
        const { ...data } = createNoteDto;

        if (await this.subscriptionBillingService.userHasActivePremiumSubscription(userId)) {
            return this.prisma.note.create({
                data: {
                    ...data,
                    userId
                }
            });
        }

        const noteCount = await this.prisma.note.count({
            where: { userId },
        });

        if (noteCount >= this.MAX_FREE_NOTES) {
            throw new ForbiddenException('Free note limit reached');
        }

        return this.prisma.note.create({
            data: {
                ...data,
                userId
            }
        });
    }

    async getNote(id: string, userId: string) {
        const note = await this.prisma.note.findUnique({ where: { id } });
        
        if (!note || note.userId !== userId) {
            throw new ForbiddenException('Note not found or access denied');
        }
        
        return note;
    }

    async updateNote(id: string, updateNoteDto: UpdateNoteDto, userId: string) {
        const note = await this.prisma.note.findUnique({ where: { id } });
        
        if (!note || note.userId !== userId) {
            throw new ForbiddenException('Note not found or access denied');
        }
        
        return this.prisma.note.update({
            where: { id },
            data: updateNoteDto
        });
    }

    async deleteNote(id: string, userId: string) {
        const note = await this.prisma.note.findUnique({ where: { id } });
        
        if (!note || note.userId !== userId) {
            throw new ForbiddenException('Note not found or access denied');
        }
        
        return this.prisma.note.delete({ where: { id } });
    }

    async getPublicNotes() {
        return this.prisma.note.findMany({
            where: { isPublic: true }
        });
    }

    async getUserNotes(userId: string) {
        return this.prisma.note.findMany({
            where: { userId }
        });
    }

    async getPublicNotesForEpisode(episodeId: string) {
        return this.prisma.note.findMany({
            where: { episodeId, isPublic: true },
        });
    }

    async attachCategoryToNote(noteId: string, categoryId: string) {
        return this.prisma.noteCategoryPivot.create({
            data: {
                noteId,
                categoryId,
            },
        });
    }
}