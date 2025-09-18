import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

interface BiddingStartedActionDto extends ActionDto {
    playerToBid: PlayerPosition;
    moveTimer: number;
}

export default BiddingStartedActionDto;