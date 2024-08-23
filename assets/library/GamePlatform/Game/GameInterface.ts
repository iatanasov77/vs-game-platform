import GamePlayersIterator from "./GamePlayersIterator";
import CardGamePlayer from './CardGamePlayer';

interface IGame
{
    players: GamePlayersIterator;
    
    initPlayers(): Array<CardGamePlayer>;
    initBoard(): void;
    startGame(): void;
    nextGame(): void;
    
}

export default IGame;
