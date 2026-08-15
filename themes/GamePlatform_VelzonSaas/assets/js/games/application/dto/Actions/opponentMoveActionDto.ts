import ActionDto from './actionDto';

import { MoveDto } from '@vankosoft/game-platform';

interface OpponentMoveActionDto extends ActionDto {
    move: MoveDto;
}

export default OpponentMoveActionDto;
