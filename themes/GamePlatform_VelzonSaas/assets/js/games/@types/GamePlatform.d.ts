declare module '_@/GamePlatform/Model/BoardGame/errorReportDto' {
    interface ErrorReportDto
    {
        error: string;
        reproduce: string;
    }
    
    export = ErrorReportDto;
}

declare module '_@/GamePlatform/Model/BoardGame/toplist' {
    import ToplistResult from '_@/GamePlatform/Model/BoardGame/toplistResult';
    
    interface Toplist
    {
        results: ToplistResult[];
        you: ToplistResult;
    }
    
    export = Toplist;
}

declare module '_@/GamePlatform/Model/BoardGame/toplistResult' {
    interface ToplistResult {
        place: number;
        name: string;
        elo: number;
        you: boolean;
    }
    
    export = ToplistResult;
}

declare module '_@/GamePlatform/Model/BoardGame/userDto' {
    interface UserDto {
        id: string;
        name: string;
        email: string;
        photoUrl: string;
        showPhoto: boolean;
        socialProvider: string;
        socialProviderId: string;
        createdNew: boolean;
        isAdmin: boolean;
        preferredLanguage: string;
        theme: string;
        emailNotification: boolean;
        gold: number;
        lastFreeGold: number;
        elo: number;
        passHash: number;
        localLoginName: string;
        acceptedLanguages: string[];
        muteIntro: boolean;
    }
    
    export = UserDto;
}

declare module '_@/GamePlatform/Model/BoardGame/connectionDto' {
    interface ConnectionDto
    {
        connected: boolean;
        pingMs: number;
    }
    
    export = ConnectionDto;
}

declare module '_@/GamePlatform/Model/BoardGame/gameDto' {
    import PlayerDto from '_@/GamePlatform/Model/BoardGame/playerDto';
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
    import GameState from '_@/GamePlatform/Model/BoardGame/gameState';
    import PointDto from '_@/GamePlatform/Model/BoardGame/pointDto';
    import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';

    interface GameDto {
        id: string;
        blackPlayer: PlayerDto;
        whitePlayer: PlayerDto;
        currentPlayer: PlayerColor;
        winner: PlayerColor;
        playState: GameState;
        points: PointDto[];
        validMoves: MoveDto[];
        thinkTime: number;
        goldMultiplier: number;
        isGoldGame: boolean;
        lastDoubler?: PlayerColor;
        stake: number;
    }
    
    export = GameDto;
}

declare module '_@/GamePlatform/Model/BoardGame/gameCookieDto' {
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
    
    interface GameCookieDto {
        id: string;
        color: PlayerColor;
        game: string;
    }
    
    export = GameCookieDto;
}

declare module '_@/GamePlatform/Model/BoardGame/moveDto' {
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
    
    interface MoveDto
    {
        color: PlayerColor;
        from: number;
        nextMoves: MoveDto[];
        to: number;
        animate: boolean;
        hint: boolean;
    }
    
    export = MoveDto;
}

declare module '_@/GamePlatform/Model/BoardGame/playerDto' {
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

    interface PlayerDto {
        name: string;
        playerColor: PlayerColor;
        pointsLeft: number;
        photoUrl: string;
        elo: number;
        gold: number;
    }
    
    export = PlayerDto;
}

declare module '_@/GamePlatform/Model/BoardGame/playerColor' {
    enum PlayerColor
    {
        black,
        white,
        neither
    }
    
    export = PlayerColor;
}

declare module '_@/GamePlatform/Model/BoardGame/pointDto' {
    import CheckerDto from '_@/GamePlatform/Model/BoardGame/checkerDto';

    interface PointDto {
        blackNumber: number;
        checkers: CheckerDto[];
        whiteNumber: number;
    }
    
    export = PointDto;
}

declare module '_@/GamePlatform/Model/BoardGame/checkerDto' {
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

    interface CheckerDto {
        color: PlayerColor;
    }
    
    export = CheckerDto;
}

declare module '_@/GamePlatform/Model/BoardGame/diceDto' {
    interface DiceDto {
        used: boolean;
        value: number;
    }
    
    export = DiceDto;
}

declare module '_@/GamePlatform/Model/BoardGame/gameState' {
    enum GameState {
        starting,
        
        opponentConnectWaiting,
        firstThrow,
        playing,
        requestedDoubling,
        ended
    }
    
    export = GameState;
}

declare module '_@/GamePlatform/Model/BoardGame/newScoreDto' {
    interface NewScoreDto {
        score: number;
        increase: number;
    }
    
    export = NewScoreDto;
}

declare module '_@/GamePlatform/Game/GameSettings' {
    type GameSettings = {
        id: string;
        publicRootPath: string;
        boardSelector: string;
        timeoutBetweenPlayers: number;
    };
    
    export = GameSettings;
}

declare module '_@/GamePlatform/Model/GameInterface' {
    interface IGame {
        id: number;
        slug: string;
        title: string;
        
        room?: any;
        deck?: any;
    }
    export = IGame;
}

declare module '_@/GamePlatform/Model/GamePlayInterface' {
    import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';
    
    interface IGamePlay {
        id: any;
        room: null | IGameRoom;
    }
    export = IGamePlay;
}

declare module '_@/GamePlatform/Model/PlayerInterface' {
    import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';
    
    interface IPlayer {
        rooms: IGameRoom[];
    
        id: number;
        type: string;
        name: string;
        connected: any;
    }
    export = IPlayer;
}

declare module '_@/GamePlatform/Model/GameRoomInterface' {
    import IGame from '_@/GamePlatform/Model/GameInterface'
    import IPlayer from '_@/GamePlatform/Model/PlayerInterface'
    
    interface IGameRoom {
        id: number;
        isPlaying: boolean;
        game: IGame;
        slug: string;
        name: string;
        players: IPlayer[];
    }
    export = IGameRoom;
}

declare module '_@/GamePlatform/Model/GameRoomModel' {
    interface IGameRoom {
        id: string;
        players: Array<any>;
    }
    export = IGameRoom;
}

declare module '_@/GamePlatform/Model/GamePlayerModel' {
    interface IGamePlayer
    {
        id: string;
        
        containerId: string;
        name: string;
        type: string;
    }
    export = IGamePlayer;
}

declare module '_@/GamePlatform/Model/GamePlayModel' {
    import IGameRoom from '_@/GamePlatform/Model/GameRoomModel';
    import IGamePlayer from '_@/GamePlatform/Model/GamePlayerModel';
    
    interface IGamePlay
    {
        id: any;
        room: null | IGameRoom;
        players: null | Iterator<IGamePlayer>;
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
    import GameSettings from '_@/GamePlatform/Game/GameSettings';
    
    class BeloteCardGame {
        constructor( gameSettings: GameSettings )
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
