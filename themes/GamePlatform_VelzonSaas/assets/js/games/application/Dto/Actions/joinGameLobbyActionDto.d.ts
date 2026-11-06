import { MercureActionDto } from './mercureActionDto';
import { GameRoomDto } from '../gameRoomDto';

export interface JoinGameLobbyActionDto extends MercureActionDto {
    room: GameRoomDto;
}
