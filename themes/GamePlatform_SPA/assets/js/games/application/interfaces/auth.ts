export interface IAuth
{
    id: number;
    
    email: string;
    username: string;
    
    fullName: string;
    
    apiToken: string;
    tokenCreated: number;
    tokenExpired: number;
}