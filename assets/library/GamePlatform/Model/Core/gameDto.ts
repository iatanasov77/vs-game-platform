import GameState from './gameState';

interface GameDto {
    id: string;
    gameCode: string;
    playState: GameState;
}

export default GameDto;
