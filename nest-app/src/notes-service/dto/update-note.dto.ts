import { IsString, MaxLength, IsOptional, IsBoolean } from 'class-validator';

export class UpdateNoteDto {
    @IsString()
    @MaxLength(255)
    @IsOptional()
    title: string;

    @IsString()
    @IsOptional()
    content: string;

    @IsOptional()
    @IsBoolean()
    isPublic?: boolean;
}
