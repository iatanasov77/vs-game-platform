import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';

interface PlayCardActionDto extends ActionDto {
    Card: CardDto;
    Belote: boolean;
    Player: PlayerPosition;
    TrickNumber: number;
}

export default PlayCardActionDto;