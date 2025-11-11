import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';
import AnnounceDto from '_@/GamePlatform/Model/CardGame/announceDto';

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