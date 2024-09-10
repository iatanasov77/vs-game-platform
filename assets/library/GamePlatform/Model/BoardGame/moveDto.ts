import PlayerColor from './playerColor';

interface MoveDto
{
    color: PlayerColor;
    from: number;
    nextMoves: MoveDto[];
    to: number;
    animate: boolean;
}

export default MoveDto;
