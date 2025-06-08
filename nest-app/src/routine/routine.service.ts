import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class RoutineService {
  constructor(private readonly prisma: PrismaService) {}

  async getAllRoutines(userId: string) {
    return this.prisma.routine.findMany({
      where: { userId },
      include: { steps: true },
      orderBy: { createdAt: 'desc' }
    });
  }

  async createRoutine(userId: string, data: any) {
    return this.prisma.routine.create({
      data: {
        ...data,
        userId
      }
    });
  }

  async updateRoutine(userId: string, routineId: string, data: any) {
    return this.prisma.routine.update({
      where: { id: routineId, userId },
      data
    });
  }

  async deleteRoutine(userId: string, routineId: string) {
    return this.prisma.routine.delete({
      where: { id: routineId, userId }
    });
  }

  async executeRoutine(routineId: string) {
    await this.prisma.routineStep.updateMany({
      where: { 
        routineId,
        isCompleted: false 
      },
      data: { isCompleted: true }
    });
  }

  async scheduleRoutine(userId: string, routineId: string, schedule: string) {
    return this.prisma.routine.update({
      where: { id: routineId, userId },
      data: { schedule }
    });
  }

  async getRoutineSteps(routineId: string) {
    return this.prisma.routineStep.findMany({
      where: { routineId },
      orderBy: { order: 'asc' }
    });
  }
}