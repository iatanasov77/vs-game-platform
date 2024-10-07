import ActionDto from './actionDto';

import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';

interface OpponentMoveActionDto extends ActionDto {
    move: MoveDto;
}

export default OpponentMoveActionDto;
