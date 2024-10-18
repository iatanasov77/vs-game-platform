import PlayerColor from './playerColor';

interface MoveDto
{
    color: PlayerColor;
    from: number;
    nextMoves: MoveDto[];
    to: number;
    animate: boolean;
    hint: boolean;
}

export default MoveDto;
