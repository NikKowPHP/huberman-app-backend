import { Controller, Get, Post, Put, Delete, Param, Body, Req, UseGuards } from '@nestjs/common';
import { SupabaseAuthGuard } from '../common/guards/supabase-auth.guard';
import { RoutineService } from './routine.service';
import { StoreRoutineDto } from './dto/store-routine.dto';
import { UpdateRoutineDto } from './dto/update-routine.dto';
import { ApiBearerAuth, ApiOperation, ApiResponse, ApiTags } from '@nestjs/swagger';

@ApiTags('routines')
@Controller('routines')
@UseGuards(SupabaseAuthGuard)
@ApiBearerAuth()
export class RoutineController {
  constructor(private readonly routineService: RoutineService) {}

  @Get()
  @ApiOperation({ summary: 'Get all routines for the authenticated user' })
  @ApiResponse({ status: 200, description: 'List of routines' })
  async getAllRoutines(@Req() req) {
    return this.routineService.getAllRoutines(req.user);
  }

  @Post()
  @ApiOperation({ summary: 'Create a new routine' })
  @ApiResponse({ status: 201, description: 'Routine created successfully' })
  async createRoutine(@Req() req, @Body() storeRoutineDto: StoreRoutineDto) {
    return this.routineService.createRoutine(req.user, storeRoutineDto);
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get a specific routine by ID' })
  @ApiResponse({ status: 200, description: 'Routine details' })
  @ApiResponse({ status: 404, description: 'Routine not found' })
  async getRoutine(@Req() req, @Param('id') id: string) {
    return this.routineService.getRoutine(req.user, id);
  }

  @Put(':id')
  @ApiOperation({ summary: 'Update a routine by ID' })
  @ApiResponse({ status: 200, description: 'Routine updated successfully' })
  @ApiResponse({ status: 404, description: 'Routine not found' })
  async updateRoutine(@Req() req, @Param('id') id: string, @Body() updateRoutineDto: UpdateRoutineDto) {
    return this.routineService.updateRoutine(req.user, id, updateRoutineDto);
  }

  @Delete(':id')
  @ApiOperation({ summary: 'Delete a routine by ID' })
  @ApiResponse({ status: 200, description: 'Routine deleted successfully' })
  @ApiResponse({ status: 404, description: 'Routine not found' })
  async deleteRoutine(@Req() req, @Param('id') id: string) {
    return this.routineService.deleteRoutine(req.user, id);
  }

  @Get(':id/steps')
  @ApiOperation({ summary: 'Get steps for a specific routine' })
  @ApiResponse({ status: 200, description: 'List of routine steps' })
  @ApiResponse({ status: 404, description: 'Routine not found' })
  async getRoutineSteps(@Req() req, @Param('id') id: string) {
    return this.routineService.getRoutineSteps(req.user, id);
  }
}