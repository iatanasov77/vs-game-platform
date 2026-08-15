import ActionDto from './actionDto';

import { MoveDto } from '@vankosoft/game-platform';

interface MovesMadeActionDto extends ActionDto {
    moves: MoveDto[];
}

export default MovesMadeActionDto;
