import ActionDto from './actionDto';

import { CardGameDto } from '@vankosoft/game-platform';

interface TrickEndedActionDto extends ActionDto {
    game: CardGameDto;
}

export default TrickEndedActionDto;
