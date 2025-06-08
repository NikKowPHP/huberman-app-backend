import { ApiProperty } from '@nestjs/swagger';

export class SyncOfflineDataDto {
  @ApiProperty({
    description: 'The key for the offline data',
    example: 'user_settings'
  })
  key: string;

  @ApiProperty({
    description: 'The JSON string value of the offline data',
    example: '{"theme": "dark", "notifications": true}'
  })
  value: string;
}