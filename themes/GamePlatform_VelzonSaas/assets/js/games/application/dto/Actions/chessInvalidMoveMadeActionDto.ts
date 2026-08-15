import ActionDto from './actionDto';

import { ChessMoveDto } from '@vankosoft/game-platform';

interface ChessInvalidMoveMadeActionDto extends ActionDto {
    move: ChessMoveDto;
}

export default ChessInvalidMoveMadeActionDto;
