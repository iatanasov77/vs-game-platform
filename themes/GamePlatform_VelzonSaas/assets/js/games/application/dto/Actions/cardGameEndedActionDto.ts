import ActionDto from './actionDto';

import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import NewScoreDto from '_@/GamePlatform/Model/Core/newScoreDto';

interface CardGameEndedActionDto extends ActionDto {
    game: CardGameDto;
    newScore: NewScoreDto;
}

export default CardGameEndedActionDto;
