import ActionDto from './actionDto';

import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

interface ChessGameStartedActionDto extends ActionDto {
    playerToMove: PlayerColor;
    moveTimer: number;
}

export default ChessGameStartedActionDto;
