import ActionDto from './actionDto';

import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import BridgeBeloteScoreDto from '_@/GamePlatform/Model/CardGame/bridgeBeloteScoreDto';

interface TrickEndedActionDto extends ActionDto {
    game: CardGameDto;
    newScore: BridgeBeloteScoreDto;
}

export default TrickEndedActionDto;

