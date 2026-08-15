import ActionDto from './actionDto';

import { BoardGameDto } from '@vankosoft/game-platform';
import { PlayerColor } from '@vankosoft/game-platform';

interface BoardGameCreatedActionDto extends ActionDto {
    game: BoardGameDto;
    myColor: PlayerColor;
}

export default BoardGameCreatedActionDto;
