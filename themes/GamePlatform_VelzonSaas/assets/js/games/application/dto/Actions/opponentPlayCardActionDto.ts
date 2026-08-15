import ActionDto from './actionDto';

import { PlayerPosition } from '@vankosoft/game-platform';
import { CardDto } from '@vankosoft/game-platform';

interface OpponentPlayCardActionDto extends ActionDto {
    Card: CardDto;
    Belote: boolean;
    Player: PlayerPosition;
    TrickNumber: number;
    
    validCards: CardDto[];
    nextPlayer: PlayerPosition;
}

export default OpponentPlayCardActionDto;