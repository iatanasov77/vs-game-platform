import IGame from './game'
import IPlayer from './player'

interface IGameRoom
{
    id: number;
    game: IGame;
    slug: string;
    name: string;
    players: IPlayer[];
}

export default IGameRoom;