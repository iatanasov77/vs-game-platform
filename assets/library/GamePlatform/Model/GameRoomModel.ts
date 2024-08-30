import IGamePlayer from './GamePlayerModel';

interface IGameRoom
{
    id: string;
    players: Array<IGamePlayer>;
}

export default IGameRoom;