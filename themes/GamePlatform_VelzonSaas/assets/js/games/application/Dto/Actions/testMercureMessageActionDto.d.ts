import { MercureActionDto } from './mercureActionDto';
import { TestMercureMessageDto } from '../testMercureMessageDto';

export interface TestMercureMessageActionDto extends MercureActionDto {
    message: TestMercureMessageDto;
}
