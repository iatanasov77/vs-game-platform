import ActionDto from './actionDto';

import { PlayerPosition } from '@vankosoft/game-platform';
import { CardDto } from '@vankosoft/game-platform';

interface PlayCardActionDto extends ActionDto {
    Card: CardDto;
    Belote: boolean;
    Player: PlayerPosition;
    TrickNumber: number;
}

export default PlayCardActionDto;