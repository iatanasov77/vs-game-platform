import ActionDto from './actionDto';

import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import BridgeBeloteScoreDto from '_@/GamePlatform/Model/CardGame/bridgeBeloteScoreDto';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';

interface RoundEndedActionDto extends ActionDto {
    game: CardGameDto;
    newScore: BridgeBeloteScoreDto;
    
    SouthNorthTricks: CardDto[];
    EastWestTricks: CardDto[];
}

export default RoundEndedActionDto;

