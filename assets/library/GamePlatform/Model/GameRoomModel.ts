import ICardGamePlayer from './CardGamePlayerModel';

interface GameRoomModel
{
    id: string;
    players: Array<ICardGamePlayer>;
}

export default GameRoomModel;