import ChessPieceType from './chessPieceType';
import PlayerColor from './playerColor';

interface ChessPieceDto
{
    Type: ChessPieceType;
    Side: PlayerColor;
    Moves: number;
}

export default ChessPieceDto;
