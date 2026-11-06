import { GameDto } from '@vankosoft/game-platform';
import { PlayerDto } from '@vankosoft/game-platform';
import { CardGameTeam } from '@vankosoft/game-platform';

export interface GameRoomDto
{
    id: number;
    owner: string;
    guid: string;
    name: string;
    
    game: GameDto,
    players: PlayerDto[],
    teams: CardGameTeam[],
    
    status: string;
    isPlaying: boolean;
}