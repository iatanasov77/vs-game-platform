interface ICardGameProvider
{
    Players: any;
    AnnounceSymbols: any;
    
    getPlayers(): Array<Object>;
    getAnnounceSymbols(): Array<Object>;
    getAnnounceSymbol( symboId: String ): void;
    getAnnounce( playerId: String ): void;
    setAnnounce( playerId: String, announceId: any ): void;
}

export default ICardGameProvider;