import { IsString, IsDate, IsIn, IsArray, ArrayNotEmpty, ValidateIf, IsBoolean, IsOptional, IsNumber } from 'class-validator';

export class StoreReminderDto {
  @IsString()
  @IsDate({ message: 'The reminder time must be in HH:MM:SS format.' })
  reminderTime: string;

  @IsString()
  @IsIn(['daily', 'weekly', 'specific_days'])
  frequency: string;

  @ValidateIf((o) => o.frequency === 'specific_days')
  @IsArray()
  @ArrayNotEmpty({ message: 'The specific days field must contain at least one day when frequency is specific_days.' })
  specificDays: string[];

  @IsString()
  message: string;

  @IsBoolean()
  @IsOptional()
  isActive: boolean;

  @IsNumber()
  @IsOptional()
  protocolId: number;
}