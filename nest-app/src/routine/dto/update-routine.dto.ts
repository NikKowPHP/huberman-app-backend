import { ApiProperty } from '@nestjs/swagger';
import { IsBoolean, IsIn, IsOptional, IsString, Matches } from 'class-validator';

export class UpdateRoutineDto {
  @ApiProperty({ description: 'Name of the routine', example: 'Morning Routine', required: false })
  @IsString()
  @IsOptional()
  name?: string;

  @ApiProperty({ description: 'Description of the routine', example: 'My morning routine to start the day', required: false })
  @IsString()
  @IsOptional()
  description?: string;

  @ApiProperty({ 
    description: 'Frequency of the routine', 
    example: 'daily',
    enum: ['daily', 'weekly', 'weekdays', 'custom'],
    required: false
  })
  @IsString()
  @IsIn(['daily', 'weekly', 'weekdays', 'custom'])
  @IsOptional()
  frequency?: string;

  @ApiProperty({ description: 'Start time in HH:mm format', example: '07:00', required: false })
  @IsString()
  @Matches(/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/, { message: 'start_time must be in HH:mm format' })
  @IsOptional()
  start_time?: string;

  @ApiProperty({ description: 'End time in HH:mm format (must be after start_time)', example: '08:00', required: false })
  @IsString()
  @Matches(/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/, { message: 'end_time must be in HH:mm format' })
  @IsOptional()
  end_time?: string;

  @ApiProperty({ description: 'Whether the routine is active', example: true, required: false })
  @IsBoolean()
  @IsOptional()
  is_active?: boolean;
}