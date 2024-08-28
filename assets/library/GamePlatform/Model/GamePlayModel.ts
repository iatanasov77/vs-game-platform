import ICardGamePlayer from './CardGamePlayerModel';

interface IGamePlay
{
    players: Iterator<ICardGamePlayer>;
    
    initPlayers(): Array<ICardGamePlayer>;
    initBoard(): void;
    startGame(): void;
    nextGame(): void;
    
}

export default IGamePlay;