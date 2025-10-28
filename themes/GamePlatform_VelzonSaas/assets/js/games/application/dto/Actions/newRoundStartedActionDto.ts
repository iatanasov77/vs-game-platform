import ActionDto from './actionDto';

import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';

interface NewRoundStartedActionDto extends ActionDto {
    game: CardGameDto;
}

export default NewRoundStartedActionDto;
