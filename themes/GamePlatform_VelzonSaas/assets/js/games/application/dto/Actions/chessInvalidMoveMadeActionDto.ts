import ActionDto from './actionDto';

import ChessMoveDto from '_@/GamePlatform/Model/BoardGame/chessMoveDto';

interface ChessInvalidMoveMadeActionDto extends ActionDto {
    move: ChessMoveDto;
}

export default ChessInvalidMoveMadeActionDto;
