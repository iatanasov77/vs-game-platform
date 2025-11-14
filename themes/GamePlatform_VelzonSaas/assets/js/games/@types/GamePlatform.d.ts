/**
 * Core Interfaces
 */
declare module '_@/GamePlatform/Model/Core/connectionDto' {
    interface ConnectionDto
    {
        connected: boolean;
        pingMs: number;
    }
    
    export = ConnectionDto;
}

declare module '_@/GamePlatform/Model/Core/errorReportDto' {
    interface ErrorReportDto
    {
        error: string;
        reproduce: string;
    }
    
    export = ErrorReportDto;
}

declare module '_@/GamePlatform/Model/Core/userDto' {
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

declare module '_@/GamePlatform/Model/Core/gameState' {
    enum GameState
    {
        opponentConnectWaiting,
        firstThrow,
        playing,
        requestedDoubling,
        ended,
        
        // Card Games States
        firstBid,
        bidding,
        firstRound,
        roundEnded
    }
    
    export = GameState;
}

declare module '_@/GamePlatform/Model/Core/gameDto' {
    import GameState from '_@/GamePlatform/Model/Core/gameState';
    
    interface GameDto
    {
        id: string;
        playState: GameState;
    }
    
    export = GameDto;
}

declare module '_@/GamePlatform/Model/Core/gameCookieDto' {
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
    import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
    
    interface GameCookieDto
    {
        id: string;
        game: string;
        
        color?: PlayerColor;
        position?: PlayerPosition;
        
        roomSelected: boolean;
    }
    
    export = GameCookieDto;
}

declare module '_@/GamePlatform/Model/Core/playerDto' {
    interface PlayerDto
    {
        name: string;
        photoUrl: string;
        
        // My Property to Detect If Player is AI in Frontend
        isAi: boolean;
    }
    
    export = PlayerDto;
}

declare module '_@/GamePlatform/Model/Core/newScoreDto' {
    interface NewScoreDto {
        score: number;
        increase: number;
    }
    
    export = NewScoreDto;
}

/**
 * BoardGame Interfaces
 */
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
    interface ToplistResult
    {
        place: number;
        name: string;
        elo: number;
        you: boolean;
    }
    
    export = ToplistResult;
}

declare module '_@/GamePlatform/Model/BoardGame/gameDto' {
    import GameDto from '_@/GamePlatform/Model/Core/gameDto';
    import GameState from '_@/GamePlatform/Model/Core/gameState';
    
    import BoardGamePlayerDto from '_@/GamePlatform/Model/BoardGame/playerDto';
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
    import PointDto from '_@/GamePlatform/Model/BoardGame/pointDto';
    import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';

    interface BoardGameDto extends GameDto
    {
        blackPlayer: BoardGamePlayerDto;
        whitePlayer: BoardGamePlayerDto;
        currentPlayer: PlayerColor;
        winner: PlayerColor;
        points: PointDto[];
        validMoves: MoveDto[];
        thinkTime: number;
        goldMultiplier: number;
        isGoldGame: boolean;
        lastDoubler?: PlayerColor;
        stake: number;
    }
    
    export = BoardGameDto;
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
    import PlayerDto from '_@/GamePlatform/Model/Core/playerDto';
    import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

    interface BoardGamePlayerDto extends PlayerDto
    {
        playerColor: PlayerColor;
        pointsLeft: number;
        elo: number;
        gold: number;
    }
    
    export = BoardGamePlayerDto;
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

/**
 * CardGame Interfaces
 */
declare module '_@/GamePlatform/Model/CardGame/gameDto' {
    import GameDto from '_@/GamePlatform/Model/Core/gameDto';
    import GameState from '_@/GamePlatform/Model/Core/gameState';
    
    import CardGamePlayerDto from '_@/GamePlatform/Model/CardGame/playerDto';
    import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
    import CardGameTeam from '_@/GamePlatform/Model/CardGame/cardGameTeam'

    interface CardGameDto extends GameDto {
        players: CardGamePlayerDto[];
        validBids: any;
        validCards: any;
        contract: any;
        
        currentPlayer: PlayerPosition;
        winner: CardGameTeam;
        thinkTime: number;
        
        FirstToPlayInTheRound: PlayerPosition;
        RoundNumber: number;
        TrickNumber: number;
        
        SouthNorthPoints: number;
        EastWestPoints: number;
        
        MyCards: any;
        Bids: any;
    }
    
    export = CardGameDto;
}

declare module '_@/GamePlatform/Model/CardGame/playerPosition' {
    enum PlayerPosition
    {
        south,
        east,
        north,
        west,
        neither
    }
    
    export = PlayerPosition;
}

declare module '_@/GamePlatform/Model/CardGame/bidType' {
    enum BidType
    {
        Pass,
        Clubs,
        Diamonds,
        Hearts,
        Spades,
        
        NoTrumps,
        AllTrumps,
        Double,
        ReDouble
    }
    
    export = BidType;
}

declare module '_@/GamePlatform/Model/CardGame/cardSuit' {
    enum CardSuit
    {
        Club,
        Diamond,
        Heart,
        Spade,
    }
    
    export = CardSuit;
}

declare module '_@/GamePlatform/Model/CardGame/cardType' {
    enum CardType
    {
        Seven,
        Eight,
        Nine,
        Ten,
        Jack,
        Queen,
        King,
        Ace,
    }
    
    export = CardType;
}

declare module '_@/GamePlatform/Model/CardGame/cardGameTeam' {
    enum CardGameTeam
    {
        SouthNorth,
        EastWest,
        Neither
    }
    
    export = CardGameTeam;
}

declare module '_@/GamePlatform/Model/CardGame/playerDto' {
    import PlayerDto from '_@/GamePlatform/Model/Core/playerDto';
    import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
    
    interface CardGamePlayerDto extends PlayerDto
    {
        playerPosition: PlayerPosition;
    }
    
    export default CardGamePlayerDto;
}

declare module '_@/GamePlatform/Model/CardGame/cardDto' {
    import CardSuit from '_@/GamePlatform/Model/CardGame/cardSuit';
    import CardType from '_@/GamePlatform/Model/CardGame/cardType';
    import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
    
    interface CardDto
    {
        Suit: CardSuit;
        Type: CardType;
        
        position: PlayerPosition;
        cardIndex: string;
        animate: boolean;
    }
    
    export default CardDto;
}

declare module '_@/GamePlatform/Model/CardGame/bidDto' {
    import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
    import BidType from '_@/GamePlatform/Model/CardGame/bidType';

    interface BidDto
    {
        Player: PlayerPosition;
        KontraPlayer?: PlayerPosition;
        ReKontraPlayer?: PlayerPosition;
        
        Type: BidType;
        NextBids: BidDto[];
    }
    
    export default BidDto;
}

declare module '_@/GamePlatform/Model/CardGame/bridgeBeloteScoreDto' {
    interface BridgeBeloteScoreDto
    {
        contract: any;
        SouthNorthPoints: number;
        SouthNorthTotalInRoundPoints: number;
        EastWestPoints: number;
        EastWestTotalInRoundPoints: number;
    }
    
    export default BridgeBeloteScoreDto;
}

/**
 * Common Interfaces
 */
declare module '_@/GamePlatform/Model/GameInterface' {
    interface IGame {
        id: number;
        slug: string;
        title: string;
        url: string;
        
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

declare module '_@/GamePlatform/Model/CardGamePlayerModel' {
    interface CardGamePlayerModel {
        id: string;
        announce: null | string;
    }
    export = CardGamePlayerModel;
}

declare module '_@/GamePlatform/Model/CardGameAnnounceSymbolModel' {
    import BidType from '_@/GamePlatform/Model/CardGame/bidType';
    
    interface CardGameAnnounceSymbolModel {
        id: BidType;
        key: string;
        tooltip: string;
        value: string;
    }
    export = CardGameAnnounceSymbolModel;
}

declare module '_@/GamePlatform/Model/CardGame/announceType' {
    enum AnnounceType
    {
        Belot,
        SequenceOf3,
        SequenceOf4,
        SequenceOf5,
        SequenceOf6,
        SequenceOf7,
        SequenceOf8,
        FourOfAKind,
        FourNines,
        FourJacks,
    }
    export = AnnounceType;
}

declare module '_@/GamePlatform/Model/CardGame/announceDto' {
    import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
    import AnnounceType from '_@/GamePlatform/Model/CardGame/announceType';
    import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
    
    interface AnnounceDto
    {
        Player: PlayerPosition;
        Type: AnnounceType;
        Card: CardDto;
    }
    export = AnnounceDto;
}
