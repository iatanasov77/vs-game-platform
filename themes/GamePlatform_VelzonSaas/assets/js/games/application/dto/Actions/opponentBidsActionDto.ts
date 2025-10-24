import ActionDto from './actionDto';

import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import GameState from '_@/GamePlatform/Model/Core/gameState';

interface OpponentBidsActionDto extends ActionDto {
    bid: BidDto;
    validBids: BidDto[];
    nextPlayer: PlayerPosition;
    playState: GameState;
}

export default OpponentBidsActionDto;
