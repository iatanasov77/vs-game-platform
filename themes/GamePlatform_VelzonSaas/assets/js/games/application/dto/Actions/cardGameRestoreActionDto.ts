import ActionDto from './actionDto';

import { CardGameDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';

interface CardGameRestoreActionDto extends ActionDto {
    game: CardGameDto;
    position: PlayerPosition;
}

export default CardGameRestoreActionDto;
