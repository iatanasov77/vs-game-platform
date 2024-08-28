declare module '_@/GamePlatform/Model/GamePlayModel' {
    interface IGamePlay
    {
        id: any;
    }
    export = IGamePlay;
}

declare module '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface' {
    interface ICardGameAnnounce
    {
        
    }
    export = ICardGameAnnounce;
}

declare module '_@/GamePlatform/Game/GameEvents' {
    export const PLAYER_ANNOUNCE_EVENT_NAME: string;
    export const GAME_START_EVENT_NAME: string;
    export const playerAnnounce: CustomEvent;
    export const gameStart: CustomEvent;
}

declare module '_@/GamePlatform/Game/BeloteCardGame' {
    class BeloteCardGame {
        constructor( boardSelector: string, publicRootPath: string )
    }
    export = BeloteCardGame;
}

declare module '_@/GamePlatform/Game/CardGamePlayer' {
    class CardGamePlayer {
        id: any;
        containerId: any;
        constructor( id: any, containerId: any, playerName: any, playerType: any )
    }
    export = CardGamePlayer;
}

declare module '_@/GamePlatform/CardGameAnnounce/Announce' {
    class Announce {
        static PASS: string;
    
        static CLOVER: string;
        static DIAMOND: string;
        static HEART: string;
        static SPADE: string;
        
        static BEZ_KOZ: string;
        static VSICHKO_KOZ: string;
        
        static KONTRA: string;
        static RE_KONTRA: string;
    }
    export = Announce;
}

declare module '_@/GamePlatform/Model/GameRoomModel' {
    interface GameRoomModel {
        id: string;
        players: Array<any>;
    }
    export = GameRoomModel;
}

declare module '_@/GamePlatform/Model/CardGamePlayerModel' {
    interface CardGamePlayerModel {
        id: string;
        announce: null | string;
    }
    export = CardGamePlayerModel;
}

declare module '_@/GamePlatform/Model/CardGameAnnounceSymbolModel' {
    interface CardGameAnnounceSymbolModel {
        id: string;
        key: string;
        tooltip: string;
        value: string;
    }
    export = CardGameAnnounceSymbolModel;
}
