import ActionDto from './actionDto';

import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import NewScoreDto from '_@/GamePlatform/Model/Core/newScoreDto';

interface BoardGameEndedActionDto extends ActionDto {
    game: BoardGameDto;
    newScore: NewScoreDto;
}

export default BoardGameEndedActionDto;
