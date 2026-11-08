import { MercureActionDto } from './mercureActionDto';
import { MercureMessageDto } from '../mercureMessageDto';

export interface MercureMessageActionDto extends MercureActionDto {
    message: MercureMessageDto;
}
