import ActionDto from './actionDto';

import { PlayerPosition } from '@vankosoft/game-platform';
import { CardDto } from '@vankosoft/game-platform';
import { BidDto } from '@vankosoft/game-platform';

interface BiddingStartedActionDto extends ActionDto {
    deck: CardDto[];
    playerCards: Array<CardDto[]>;
    firstToBid: PlayerPosition;
    validBids: BidDto[];
    timer: number;
}

export default BiddingStartedActionDto;