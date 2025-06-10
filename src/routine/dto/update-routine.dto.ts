import { ApiProperty } from '@nestjs/swagger';
import { IsString, IsOptional, IsArray } from 'class-validator';

export class UpdateRoutineDto {
  @ApiProperty({ description: 'Updated name of the routine', example: 'Updated Morning Routine', required: false })
  @IsString()
  @IsOptional()
  name?: string;

  @ApiProperty({ description: 'Updated description of the routine', example: 'Updated morning wellness routine', required: false })
  @IsString()
  @IsOptional()
  description?: string;

  @ApiProperty({ description: 'Updated array of step descriptions', example: ['Wake up early', 'Drink lemon water'], required: false })
  @IsArray()
  @IsOptional()
  steps?: string[];
}