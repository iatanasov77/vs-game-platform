import ActionDto from './actionDto';

import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
//import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';

interface CardGameRestoreActionDto extends ActionDto {
    game: CardGameDto;
    position: PlayerPosition;
    //dices: DiceDto[];
}

export default CardGameRestoreActionDto;
