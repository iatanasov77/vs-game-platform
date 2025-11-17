import ActionDto from './actionDto';

import ChessMoveDto from '_@/GamePlatform/Model/BoardGame/chessMoveDto';

interface ChessOpponentMoveActionDto extends ActionDto {
    move: ChessMoveDto;
}

export default ChessOpponentMoveActionDto;
