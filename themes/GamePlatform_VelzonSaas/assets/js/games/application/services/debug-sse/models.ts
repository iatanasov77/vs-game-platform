export interface User
{
    name: string;
    id: string;
}

export interface Topic
{
    getTopic( user: User ): string;
}

export class PaymentTopic implements Topic
{
    getTopic( user: User ): string {
        return 'https://topics.domain.com/' + user.id + '/payments';
    }
}

export interface MessageData
{
    status: string;
    message: string;
}
