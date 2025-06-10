import { IsNotEmpty, IsString } from 'class-validator';

export class AttachCategoryDto {
    @IsNotEmpty()
    @IsString()
    category_id: string;
}
