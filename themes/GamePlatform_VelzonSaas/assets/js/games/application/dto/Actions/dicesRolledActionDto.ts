import ActionDto from './actionDto';

import { DiceDto } from '@vankosoft/game-platform';
import { PlayerColor } from '@vankosoft/game-platform';
import { MoveDto } from '@vankosoft/game-platform';

interface DicesRolledActionDto extends ActionDto {
    dices: DiceDto[];
    playerToMove: PlayerColor;
    validMoves: MoveDto[];
    moveTimer: number;
}

export default DicesRolledActionDto;
