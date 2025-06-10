import { IsInt, IsDateString, IsString, IsOptional, IsArray, ValidateNested, MaxLength } from 'class-validator';
import { Type } from 'class-transformer';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class StoreTrackingLogDto {
  @ApiProperty({ description: 'ID of the protocol being tracked', example: 1 })
  @IsInt()
  protocol_id: number;

  @ApiProperty({ description: 'Date when the protocol was tracked (YYYY-MM-DD format)', example: '2025-06-08' })
  @IsDateString()
  tracked_at: string;

  @ApiPropertyOptional({ description: 'Optional notes about the tracking entry', example: 'Completed morning sunlight protocol', maxLength: 10000 })
  @IsOptional()
  @IsString()
  @MaxLength(10000)
  notes?: string;

  @ApiPropertyOptional({ description: 'Optional metadata about the tracking entry', type: Object })
  @IsOptional()
  @IsArray()
  @ValidateNested({ each: true })
  @Type(() => Object)
  metadata?: Record<string, any>[];
}