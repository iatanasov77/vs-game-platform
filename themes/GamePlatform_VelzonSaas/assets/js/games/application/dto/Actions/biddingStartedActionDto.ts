import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';

interface BiddingStartedActionDto extends ActionDto {
    playerCards: Array<CardDto[]>;
    playerToBid: PlayerPosition;
    validBids: BidDto[];
    bidTimer: number;
}

export default BiddingStartedActionDto;