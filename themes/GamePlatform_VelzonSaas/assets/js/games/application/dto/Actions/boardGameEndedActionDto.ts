import ActionDto from './actionDto';

import { BoardGameDto } from '@vankosoft/game-platform';
import { NewScoreDto } from '@vankosoft/game-platform';

interface BoardGameEndedActionDto extends ActionDto {
    game: BoardGameDto;
    newScore: NewScoreDto;
}

export default BoardGameEndedActionDto;
