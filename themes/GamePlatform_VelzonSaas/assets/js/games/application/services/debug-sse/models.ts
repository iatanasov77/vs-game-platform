import { Topic } from '@vankosoft/game-platform';

export interface MercureJwt
{
    token: string;
}

export interface User
{
    name: string;
    id: string;
}

export class PaymentTopic implements Topic
{
    url: string;
    
    constructor( url: string )
    {
        this.url = url;
    }
    
    getTopic(): string
    {
        return this.url;
    }
}
