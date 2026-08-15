import ActionDto from './actionDto';

import { BidDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';
import { GameState } from '@vankosoft/game-platform';
import { CardDto } from '@vankosoft/game-platform';

interface OpponentBidsActionDto extends ActionDto {
    bid: BidDto;
    validBids: BidDto[];
    nextPlayer: PlayerPosition;
    playState: GameState;
    
    MyCards: CardDto[] | undefined;
}

export default OpponentBidsActionDto;
