import { MercureActionDto } from './mercureActionDto';

export interface CreateGameLobbyActionDto extends MercureActionDto {
    gameType: string;
}
