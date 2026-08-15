import ActionDto from './actionDto';

import { ChessMoveDto } from '@vankosoft/game-platform';
import { BoardGameDto } from '@vankosoft/game-platform';
import { PlayerColor } from '@vankosoft/game-platform';

interface ChessOpponentMoveActionDto extends ActionDto {
    move?: ChessMoveDto;
    myColor: PlayerColor;
    
    game?: BoardGameDto;
    moveTimer?: number;
}

export default ChessOpponentMoveActionDto;
