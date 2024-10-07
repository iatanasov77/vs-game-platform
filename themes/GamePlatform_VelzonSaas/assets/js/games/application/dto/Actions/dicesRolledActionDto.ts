import ActionDto from './actionDto';

import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';

interface DicesRolledActionDto extends ActionDto {
    dices: DiceDto[];
    playerToMove: PlayerColor;
    validMoves: MoveDto[];
    moveTimer: number;
}

export default DicesRolledActionDto;
