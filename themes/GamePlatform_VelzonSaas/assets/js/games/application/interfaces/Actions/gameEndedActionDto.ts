import ActionDto from './actionDto';

import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import NewScoreDto from '_@/GamePlatform/Model/BoardGame/newScoreDto';

interface GameEndedActionDto extends ActionDto {
    game: GameDto;
    newScore: NewScoreDto;
}

export default GameEndedActionDto;
