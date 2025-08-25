import PlayerDto from '../Core/playerDto';
import PlayerColor from './playerColor';

interface BoardGamePlayerDto extends PlayerDto {
    playerColor: PlayerColor;
    pointsLeft: number;
    elo: number;
    gold: number;
}

export default BoardGamePlayerDto;
