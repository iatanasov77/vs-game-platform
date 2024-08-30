import IGameRoom from './GameRoomModel';
import IGamePlayer from './GamePlayerModel';

interface IGamePlay
{
    id: any;
    room: null | IGameRoom;
    
    players?: Iterator<IGamePlayer>;
    
    initPlayers(): Array<IGamePlayer>;
    initBoard(): void;
    startGame(): void;
    nextGame(): void;
    
}

export default IGamePlay;