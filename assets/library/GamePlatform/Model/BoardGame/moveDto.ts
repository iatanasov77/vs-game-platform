import PlayerColor from './playerColor';

interface MoveDto
{
    color: PlayerColor;
    from: number;
    to: number;
    nextMoves: MoveDto[];
    animate: boolean;
    hint: boolean;
}

export default MoveDto;
