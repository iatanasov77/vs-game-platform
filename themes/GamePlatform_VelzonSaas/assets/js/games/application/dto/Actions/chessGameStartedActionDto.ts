import ActionDto from './actionDto';

import { PlayerColor } from '@vankosoft/game-platform';
import { BoardGameDto } from '@vankosoft/game-platform';

interface ChessGameStartedActionDto extends ActionDto {
    playerToMove: PlayerColor;
    moveTimer: number;
    game: BoardGameDto;
}

export default ChessGameStartedActionDto;
