import { IsString, IsDate, IsIn, IsArray, ArrayNotEmpty, ValidateIf, IsBoolean, IsOptional, IsNumber } from 'class-validator';

export class UpdateReminderDto {
  @IsString()
  @IsDate({ message: 'The reminder time must be in HH:MM:SS format.' })
  @IsOptional()
  reminderTime: string;

  @IsString()
  @IsIn(['daily', 'weekly', 'specific_days'])
  @IsOptional()
  frequency: string;

  @ValidateIf((o) => o.frequency === 'specific_days')
  @IsArray()
  @ArrayNotEmpty({ message: 'The specific days field must contain at least one day when frequency is specific_days.' })
  @IsOptional()
  specificDays: string[];

  @IsString()
  @IsOptional()
  message: string;

  @IsBoolean()
  @IsOptional()
  isActive: boolean;

  @IsNumber()
  @IsOptional()
  protocolId: number;
}