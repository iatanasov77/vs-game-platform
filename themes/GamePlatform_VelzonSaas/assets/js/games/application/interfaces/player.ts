import { IGameRoom } from './game-room';

export interface IPlayer
{
    rooms: IGameRoom[];
    
    id: number;
    type: string;
    name: string;
    connected: any;
}