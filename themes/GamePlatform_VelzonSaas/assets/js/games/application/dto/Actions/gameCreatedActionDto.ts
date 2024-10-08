import ActionDto from './actionDto';

import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

interface GameCreatedActionDto extends ActionDto {
    game: GameDto;
    myColor: PlayerColor;
}

export default GameCreatedActionDto;
