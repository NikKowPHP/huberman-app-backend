import { IsString, IsNotEmpty, IsOptional, IsBoolean } from 'class-validator';

export class CreateProtocolDto {
    @IsString()
    @IsNotEmpty()
    title: string;

    @IsString()
    @IsOptional()
    description?: string;

    @IsString()
    @IsOptional()
    implementationGuide?: string;

    @IsString()
    @IsOptional()
    category?: string;

    @IsBoolean()
    @IsOptional()
    isFree?: boolean;
}
