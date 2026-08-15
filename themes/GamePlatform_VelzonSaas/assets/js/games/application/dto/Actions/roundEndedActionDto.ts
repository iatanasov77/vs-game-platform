import ActionDto from './actionDto';

import { CardGameDto } from '@vankosoft/game-platform';
import { BridgeBeloteScoreDto } from '@vankosoft/game-platform';
import { CardDto } from '@vankosoft/game-platform';

interface RoundEndedActionDto extends ActionDto {
    game: CardGameDto;
    newScore: BridgeBeloteScoreDto;
    
    SouthNorthTricks: CardDto[];
    EastWestTricks: CardDto[];
}

export default RoundEndedActionDto;

