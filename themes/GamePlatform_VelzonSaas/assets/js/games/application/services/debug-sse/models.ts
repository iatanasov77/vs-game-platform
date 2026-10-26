import { Topic } from '@vankosoft/game-platform';

export class JwtToken
{
    static FactorySymfonyMercure    = 'simfony_mercure';
    static FactoryFirebaseJwt       = 'firebase_jwt';
}

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
