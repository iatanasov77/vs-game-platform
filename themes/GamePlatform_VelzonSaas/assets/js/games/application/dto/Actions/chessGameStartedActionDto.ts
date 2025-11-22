import ActionDto from './actionDto';

import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';

interface ChessGameStartedActionDto extends ActionDto {
    playerToMove: PlayerColor;
    moveTimer: number;
    game: BoardGameDto;
}

export default ChessGameStartedActionDto;
