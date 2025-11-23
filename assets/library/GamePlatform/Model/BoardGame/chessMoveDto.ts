import PlayerColor from './playerColor';
import ChessMoveType from './chessMoveType';
import ChessPieceType from './chessPieceType';

interface ChessMoveDto
{
    color: PlayerColor;
    type: ChessMoveType;
    from: string;
    to: string;
    
    causeCheck: boolean;
    
    piece: ChessPieceType;
    capturedPiece?: ChessPieceType;
    promoPiece?: ChessPieceType;
    enpassantPiece?: ChessPieceType;
    
    nextMoves: ChessMoveDto[];
    animate: boolean;
    hint: boolean;
}

export default ChessMoveDto;
