import { IsString, MaxLength } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreatePostDto {
  @ApiProperty({
    description: 'The title of the post',
    maxLength: 255,
    example: 'My First Post'
  })
  @IsString()
  @MaxLength(255)
  title: string;

  @ApiProperty({
    description: 'The content of the post',
    example: 'This is my first post content'
  })
  @IsString()
  content: string;
}