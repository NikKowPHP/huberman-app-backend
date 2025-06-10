import { ApiProperty } from '@nestjs/swagger';
import { IsString, IsNotEmpty, IsOptional, IsArray } from 'class-validator';

export class StoreRoutineDto {
  @ApiProperty({ description: 'Name of the routine', example: 'Morning Routine' })
  @IsString()
  @IsNotEmpty()
  name: string;

  @ApiProperty({ description: 'Description of the routine', example: 'My morning wellness routine', required: false })
  @IsString()
  @IsOptional()
  description?: string;

  @ApiProperty({ description: 'Array of step descriptions', example: ['Wake up', 'Drink water'], required: false })
  @IsArray()
  @IsOptional()
  steps?: string[];
}