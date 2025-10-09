import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';

interface BiddingStartedActionDto extends ActionDto {
    deck: CardDto[];
    playerCards: Array<CardDto[]>;
    firstToBid: PlayerPosition;
    validBids: BidDto[];
    timer: number;
}

export default BiddingStartedActionDto;