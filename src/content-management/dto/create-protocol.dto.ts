import { IsString, IsNotEmpty, IsOptional, IsBoolean } from 'class-validator';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class CreateProtocolDto {
    @IsString()
    @IsNotEmpty()
    @ApiProperty({
        description: 'The title of the protocol',
        example: 'Morning Sunlight Protocol',
    })
    title: string;

    @IsString()
    @IsOptional()
    @ApiPropertyOptional({
        description: 'A brief description of the protocol',
        example: 'A protocol for optimizing morning sunlight exposure',
    })
    description?: string;

    @IsString()
    @IsOptional()
    @ApiPropertyOptional({
        description: 'Detailed implementation guide for the protocol',
        example: 'Step-by-step instructions...',
    })
    implementationGuide?: string;

    @IsString()
    @IsOptional()
    @ApiPropertyOptional({
        description: 'Category of the protocol',
        example: 'Circadian Rhythm',
    })
    category?: string;

    @IsBoolean()
    @IsOptional()
    @ApiPropertyOptional({
        description: 'Whether the protocol is free or requires premium access',
        example: true,
    })
    isFree?: boolean;
}
