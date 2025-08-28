import ActionDto from './actionDto';

import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

interface StartGamePlayActionDto extends ActionDto {
    game: BoardGameDto;
    myColor: PlayerColor;
}

export default StartGamePlayActionDto;