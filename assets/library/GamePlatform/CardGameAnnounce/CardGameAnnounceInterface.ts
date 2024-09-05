interface ICardGameAnnounce
{
    announce( hand: any, lastAnnounce: any ): string;
    getAnnounce( announces: any ): string;
}

export default ICardGameAnnounce;
