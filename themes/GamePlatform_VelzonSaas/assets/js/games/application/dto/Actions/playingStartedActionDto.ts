import ActionDto from './actionDto';

import { PlayerPosition } from '@vankosoft/game-platform';
import { CardDto } from '@vankosoft/game-platform';
import { BidDto } from '@vankosoft/game-platform';
import { AnnounceDto } from '@vankosoft/game-platform';

interface PlayingStartedActionDto extends ActionDto {
    deck: CardDto[];
    playerCards: Array<CardDto[]>;
    playerAnnounces: Array<AnnounceDto[]>;
    firstToPlay: PlayerPosition;
    contract: BidDto
    validCards: CardDto[];
    timer: number;
}

export default PlayingStartedActionDto;