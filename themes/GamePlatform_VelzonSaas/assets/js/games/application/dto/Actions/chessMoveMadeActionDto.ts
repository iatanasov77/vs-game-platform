import ActionDto from './actionDto';

import ChessMoveDto from '_@/GamePlatform/Model/BoardGame/chessMoveDto';

interface ChessMoveMadeActionDto extends ActionDto {
    move?: ChessMoveDto;
}

export default ChessMoveMadeActionDto;
