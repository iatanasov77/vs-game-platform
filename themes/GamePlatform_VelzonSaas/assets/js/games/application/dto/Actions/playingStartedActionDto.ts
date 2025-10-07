import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';

interface PlayingStartedActionDto extends ActionDto {
    playerCards: Array<CardDto[]>;
    firstToPlay: PlayerPosition;
    contract: BidDto
    validCards: CardDto[];
    timer: number;
}

export default PlayingStartedActionDto;