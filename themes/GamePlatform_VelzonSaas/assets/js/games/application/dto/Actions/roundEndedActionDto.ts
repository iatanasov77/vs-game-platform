import ActionDto from './actionDto';

import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import BridgeBeloteScoreDto from '_@/GamePlatform/Model/CardGame/bridgeBeloteScoreDto';

interface RoundEndedActionDto extends ActionDto {
    game: CardGameDto;
    newScore: BridgeBeloteScoreDto;
}

export default RoundEndedActionDto;

