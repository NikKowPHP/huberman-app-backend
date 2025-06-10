import { IsString, MaxLength, IsOptional, IsBoolean } from 'class-validator';

export class CreateNoteDto {
    @IsString()
    @MaxLength(255)
    title: string;

    @IsString()
    content: string;

    @IsOptional()
    @IsBoolean()
    isPublic?: boolean;

    @IsString()
    userId: string;
}
