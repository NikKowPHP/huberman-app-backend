import { IsString, MaxLength, IsOptional } from 'class-validator';

export class UpdateNoteDto {
  @IsString()
  @MaxLength(255)
  @IsOptional()
  title: string;

  @IsString()
  @IsOptional()
  content: string;
}