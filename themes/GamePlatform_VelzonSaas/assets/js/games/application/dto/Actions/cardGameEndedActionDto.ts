import ActionDto from './actionDto';

import { CardGameDto } from '@vankosoft/game-platform';
import { NewScoreDto } from '@vankosoft/game-platform';

interface CardGameEndedActionDto extends ActionDto {
    game: CardGameDto;
    newScore: NewScoreDto;
}

export default CardGameEndedActionDto;
