import { MercureActionDto } from './mercureActionDto';
import { GameRoomDto } from '../gameRoomDto';

export interface GameLobbyCreatedActionDto extends MercureActionDto {
    room: GameRoomDto;
}
