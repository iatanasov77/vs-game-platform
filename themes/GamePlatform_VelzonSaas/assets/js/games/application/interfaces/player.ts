import { IRoom } from './room';

export interface IPlayer
{
    rooms: IRoom[];
    
    id: number;
    type: string;
    name: string;
    
    __v: number;
}