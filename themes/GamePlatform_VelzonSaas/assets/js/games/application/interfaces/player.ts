import IGameRoom from './game-room';

interface IPlayer
{
    rooms: IGameRoom[];
    
    id: number;
    type: string;
    name: string;
    connected: any;
}

export default IPlayer;