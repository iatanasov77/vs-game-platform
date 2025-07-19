import PlayerColor from './playerColor';

interface PlayerDto {
    name: string;
    playerColor: PlayerColor;
    pointsLeft: number;
    photoUrl: string;
    elo: number;
    gold: number;
    
    // My Property to Detect If Player is AI in Frontend
    isAi: boolean;
}

export default PlayerDto;
