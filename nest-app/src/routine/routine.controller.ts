import { Controller, Get, Post, Body, Param, Put, Delete, UseGuards, Req } from '@nestjs/common';
import { RoutineService } from './routine.service';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';

@Controller('routines')
@UseGuards(SupabaseAuthGuard)
export class RoutineController {
  constructor(private readonly routineService: RoutineService) {}

  @Get()
  async getAllRoutines(@Req() req) {
    return this.routineService.getAllRoutines(req.user.sub);
  }

  @Post()
  async createRoutine(@Req() req, @Body() data: any) {
    return this.routineService.createRoutine(req.user.sub, data);
  }

  @Get(':id')
  async getRoutine(@Req() req, @Param('id') routineId: string) {
    return this.routineService.getAllRoutines(req.user.sub).then(routines => 
      routines.find(r => r.id === routineId)
    );
  }

  @Put(':id')
  async updateRoutine(@Req() req, @Param('id') routineId: string, @Body() data: any) {
    return this.routineService.updateRoutine(req.user.sub, routineId, data);
  }

  @Delete(':id')
  async deleteRoutine(@Req() req, @Param('id') routineId: string) {
    return this.routineService.deleteRoutine(req.user.sub, routineId);
  }

  @Get(':id/steps')
  async getRoutineSteps(@Param('id') routineId: string) {
    return this.routineService.getRoutineSteps(routineId);
  }
}