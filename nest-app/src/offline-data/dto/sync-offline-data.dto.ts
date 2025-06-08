import { ApiProperty } from '@nestjs/swagger';

class OfflineDataItemDto {
  @ApiProperty({
    description: 'The key for the offline data',
    example: 'user_settings'
  })
  key: string;

  @ApiProperty({
    description: 'The JSON string value of the offline data',
    example: '{"theme": "dark", "notifications": true}',
    required: false
  })
  value?: string;
}

export class SyncOfflineDataDto {
  @ApiProperty({
    description: 'Array of offline data items to sync',
    type: [OfflineDataItemDto]
  })
  data: OfflineDataItemDto[];
}