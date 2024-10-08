import PlayerColor from './playerColor';

interface PlayerDto {
    name: string;
    playerColor: PlayerColor;
    pointsLeft: number;
    photoUrl: string;
    elo: number;
    gold: number;
}

export default PlayerDto;
