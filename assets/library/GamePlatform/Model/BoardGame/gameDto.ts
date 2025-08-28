import GameDto from '../Core/gameDto';
import BoardGamePlayerDto from './playerDto';
import PlayerColor from './playerColor';
import PointDto from './pointDto';
import MoveDto from './moveDto';

interface BoardGameDto extends GameDto {
    blackPlayer: BoardGamePlayerDto;
    whitePlayer: BoardGamePlayerDto;
    currentPlayer: PlayerColor;
    winner: PlayerColor;
    points: PointDto[];
    validMoves: MoveDto[];
    thinkTime: number;
    goldMultiplier: number;
    isGoldGame: boolean;
    lastDoubler?: PlayerColor;
    stake: number;
}

export default BoardGameDto;
