import ActionDto from './actionDto';

import { BoardGameDto } from '@vankosoft/game-platform';
import { PlayerColor } from '@vankosoft/game-platform';
import { DiceDto } from '@vankosoft/game-platform';

interface BoardGameRestoreActionDto extends ActionDto {
    game: BoardGameDto;
    color: PlayerColor;
    dices: DiceDto[];
}

export default BoardGameRestoreActionDto;
