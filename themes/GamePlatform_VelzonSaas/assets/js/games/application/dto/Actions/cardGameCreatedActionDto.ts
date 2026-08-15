import ActionDto from './actionDto';

import { CardGameDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';

interface CardGameCreatedActionDto extends ActionDto {
    game: CardGameDto;
    myPosition: PlayerPosition;
}

export default CardGameCreatedActionDto;
