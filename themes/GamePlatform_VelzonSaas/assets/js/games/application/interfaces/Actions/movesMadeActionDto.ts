import ActionDto from './actionDto';

import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';

interface MovesMadeActionDto extends ActionDto {
    moves: MoveDto[];
}

export default MovesMadeActionDto;
