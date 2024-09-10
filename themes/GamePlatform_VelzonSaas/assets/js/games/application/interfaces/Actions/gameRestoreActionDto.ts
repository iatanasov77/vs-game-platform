import ActionDto from './actionDto';

import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';

interface GameRestoreActionDto extends ActionDto {
    game: GameDto;
    color: PlayerColor;
    dices: DiceDto[];
}

export default GameRestoreActionDto;
