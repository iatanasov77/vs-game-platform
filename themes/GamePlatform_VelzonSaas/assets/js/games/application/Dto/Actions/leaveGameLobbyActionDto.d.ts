import { MercureActionDto } from './mercureActionDto';
import { GameRoomDto } from '../gameRoomDto';

export interface LeaveGameLobbyActionDto extends MercureActionDto {
    room: GameRoomDto;
}
