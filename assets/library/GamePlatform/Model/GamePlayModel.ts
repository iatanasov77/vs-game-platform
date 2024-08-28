import IGamePlayer from './GamePlayerModel';

interface IGamePlay
{
    id: any;
    players: Iterator<IGamePlayer>;
    
    initPlayers(): Array<IGamePlayer>;
    initBoard(): void;
    startGame(): void;
    nextGame(): void;
    
}

export default IGamePlay;