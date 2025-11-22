import ChessPieceDto from './chessPieceDto';

interface ChessSquareDto
{
    Rank: number;
    File: string;
    Piece?: ChessPieceDto;
}

export default ChessSquareDto;
