import ActionDto from './actionDto';

import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

interface CardGameCreatedActionDto extends ActionDto {
    game: CardGameDto;
    myPosition: PlayerPosition;
}

export default CardGameCreatedActionDto;
