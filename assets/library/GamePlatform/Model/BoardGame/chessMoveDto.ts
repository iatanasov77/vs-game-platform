import PlayerColor from './playerColor';

interface ChessMoveDto
{
    color: PlayerColor;
    from: string;
    to: string;
    nextMoves: ChessMoveDto[];
    animate: boolean;
    hint: boolean;
}

export default ChessMoveDto;
