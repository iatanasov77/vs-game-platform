import ActionDto from './actionDto';

import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';

interface BoardGameRestoreActionDto extends ActionDto {
    game: BoardGameDto;
    color: PlayerColor;
    dices: DiceDto[];
}

export default BoardGameRestoreActionDto;
