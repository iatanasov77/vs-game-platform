import IGameRoom from './GameRoomInterface';

interface IPlayer
{
    rooms: IGameRoom[];
    
    id: number;
    type: string;
    name: string;
    connected: any;
}

export default IPlayer;