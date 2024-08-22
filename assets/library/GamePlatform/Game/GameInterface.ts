import GamePlayersIterator from "./GamePlayersIterator";

interface IGame
{
    players: GamePlayersIterator;
    
    initBoard(): void;
    startGame(): void;
    nextGame(): void;
    
}

export default IGame;
