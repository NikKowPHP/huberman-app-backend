import { IsString } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class StoreCommentDto {
  @ApiProperty({
    description: 'The content of the comment',
    example: 'This is a great post!'
  })
  @IsString()
  content: string;
}