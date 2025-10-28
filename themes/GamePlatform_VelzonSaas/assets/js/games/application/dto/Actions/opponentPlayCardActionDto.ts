import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';

interface OpponentPlayCardActionDto extends ActionDto {
    Card: CardDto;
    Belote: boolean;
    Player: PlayerPosition;
    TrickNumber: number;
    
    validCards: CardDto[];
    nextPlayer: PlayerPosition;
}

export default OpponentPlayCardActionDto;