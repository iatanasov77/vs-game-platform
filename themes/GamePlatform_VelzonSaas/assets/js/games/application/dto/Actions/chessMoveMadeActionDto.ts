import ActionDto from './actionDto';

import { ChessMoveDto } from '@vankosoft/game-platform';

interface ChessMoveMadeActionDto extends ActionDto {
    move?: ChessMoveDto;
}

export default ChessMoveMadeActionDto;
