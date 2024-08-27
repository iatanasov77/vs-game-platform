import CardGamePlayerModel from './CardGamePlayerModel';

interface GameRoomModel
{
    id: string;
    players: Array<CardGamePlayerModel>;
}

export default GameRoomModel;