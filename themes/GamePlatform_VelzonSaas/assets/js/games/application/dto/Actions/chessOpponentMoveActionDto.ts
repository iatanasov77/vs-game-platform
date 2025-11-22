import ActionDto from './actionDto';

import ChessMoveDto from '_@/GamePlatform/Model/BoardGame/chessMoveDto';
import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

interface ChessOpponentMoveActionDto extends ActionDto {
    move: ChessMoveDto;
    myColor: PlayerColor;
    
    game?: BoardGameDto;
}

export default ChessOpponentMoveActionDto;
