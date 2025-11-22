import { Injectable } from '@angular/core';

// DTO
import { StatusMessage } from '../dto/local/status-message';
import { FeedbackDto } from '../dto/feedback/feedbackDto';
import { PlayedGameListDto } from '../dto/admin/playedGameListDto';
import MessageDto from '../dto/message/messageDto';
import ChatMessageDto from '../dto/chat/chatMessageDto';

// Core Interfaces
import ConnectionDto from '_@/GamePlatform/Model/Core/connectionDto';
import UserDto from '_@/GamePlatform/Model/Core/userDto';
import GameState from '_@/GamePlatform/Model/Core/gameState';
import GameDto from '_@/GamePlatform/Model/Core/gameDto';

// BoardGame Interfaces
import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';
import Toplist from '_@/GamePlatform/Model/BoardGame/toplist';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import ChessMoveDto from '_@/GamePlatform/Model/BoardGame/chessMoveDto';

// CardGame Interfaces
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';
import AnnounceDto from '_@/GamePlatform/Model/CardGame/announceDto';
import BridgeBeloteScoreDto from '_@/GamePlatform/Model/CardGame/bridgeBeloteScoreDto';

// State
import { StateObject } from './state-object';
import { Busy } from './busy';
import { ErrorState } from './ErrorState';

declare var $: any;

@Injectable({
    providedIn: 'root'
})
export class AppStateService
{
    public static Themes = ['dark', 'light', 'blue', 'pink', 'green'];
    
    busy: StateObject<Busy>;
    
    boardGame: StateObject<BoardGameDto>;
    myColor: StateObject<PlayerColor>;
    
    cardGame: StateObject<CardGameDto>;
    myPosition: StateObject<PlayerPosition>;
    
    dices: StateObject<DiceDto[]>;
    moveAnimations: StateObject<MoveDto[]>;
    myConnection: StateObject<ConnectionDto>;
    opponentConnection: StateObject<ConnectionDto>;
    user: StateObject<UserDto>;
    statusMessage: StateObject<StatusMessage>;
    moveTimer: StateObject<number>;
    toplist: StateObject<Toplist>;
    errors: StateObject<ErrorState>;
    playedGames: StateObject<PlayedGameListDto>;
    messages: StateObject<MessageDto[]>;
    rolled: StateObject<boolean>;
    opponentDone: StateObject<boolean>;
    theme: StateObject<string>;
    tutorialStep: StateObject<number>;
    newVersion: StateObject<boolean>;
    feedbackList: StateObject<FeedbackDto[]>;
    gameString: StateObject<string>;
    chatOpen: StateObject<boolean>;
    chatMessages: StateObject<ChatMessageDto[]>;
    chatUsers: StateObject<string[]>;
    
    playerCards: StateObject<Array<CardDto[]>>;
    playerBids: StateObject<BidDto[]>;
    playerAnnounces: StateObject<Array<AnnounceDto[]>>;
    deck: StateObject<CardDto[]>;
    pile: StateObject<CardDto[]>;
    bridgeBeloteScore: StateObject<BridgeBeloteScoreDto>;
    
    chessOpponentMove: StateObject<ChessMoveDto>;
    
    constructor()
    {
        this.busy = new StateObject<Busy>();
        
        this.boardGame = new StateObject<BoardGameDto>();
        this.myColor = new StateObject<PlayerColor>();
        this.myColor.setValue( PlayerColor.neither );
        
        this.cardGame = new StateObject<CardGameDto>();
        this.myPosition = new StateObject<PlayerPosition>();
        this.myPosition.setValue( PlayerPosition.neither );
        
        this.dices = new StateObject<DiceDto[]>();
        this.dices.setValue( [] );
        this.moveAnimations = new StateObject<MoveDto[]>();
        this.moveAnimations.setValue( [] );
        this.myConnection = new StateObject<ConnectionDto>();
        this.opponentConnection = new StateObject<ConnectionDto>();
        this.user = new StateObject<UserDto>();
        this.statusMessage = new StateObject<StatusMessage>();
        this.moveTimer = new StateObject<number>();
        this.toplist = new StateObject<Toplist>();
        this.errors = new StateObject<ErrorState>();
        this.playedGames = new StateObject<PlayedGameListDto>();
        this.playedGames.setValue( { games: [] } );
        this.messages = new StateObject<MessageDto[]>();
        this.messages.setValue( [] );
        this.rolled = new StateObject<boolean>();
        this.opponentDone = new StateObject<boolean>();
        this.theme = new StateObject<string>();
        this.theme.setValue( 'dark' );
        this.tutorialStep = new StateObject<number>();
        this.tutorialStep.setValue( 0 );
        this.newVersion = new StateObject<boolean>();
        this.newVersion.setValue(false);
        this.feedbackList = new StateObject<FeedbackDto[]>();
        this.feedbackList.setValue( [] );
        this.gameString = new StateObject<string>();
        
        this.chatOpen = new StateObject<boolean>();
        this.chatMessages = new StateObject<ChatMessageDto[]>();
        this.chatMessages.setValue( [] );
        this.chatUsers = new StateObject<string[]>();
        this.chatUsers.setValue( [] );
        
        this.playerCards = new StateObject<Array<CardDto[]>>();
        this.playerCards.setValue( [] );
        this.playerBids = new StateObject<BidDto[]>();
        this.playerBids.setValue( [] );
        this.playerAnnounces = new StateObject<Array<AnnounceDto[]>>();
        this.playerAnnounces.setValue( [] );
        this.deck = new StateObject<CardDto[]>();
        this.deck.setValue( [] );
        this.pile = new StateObject<CardDto[]>();
        this.pile.setValue( [] );
        this.bridgeBeloteScore = new StateObject<BridgeBeloteScoreDto>();
        
        this.chessOpponentMove = new StateObject<ChessMoveDto>();
    }

    myTurn(): boolean
    {
        const game = this.boardGame.getValue();
        
        return (
            game &&
            game.playState !== GameState.ended &&
            game.currentPlayer === this.myColor.getValue()
        );
    }
    
    doublingRequested(): boolean
    {
        const game = this.boardGame.getValue();
        return game && game.playState === GameState.requestedDoubling;
    }
    
    getOtherPlayer(): PlayerColor
    {
        return this.myColor.getValue() === PlayerColor.black
          ? PlayerColor.white
          : PlayerColor.black;
    }
    
    showBusy(): void
    {
        this.busy.setValue( new Busy( 'Please wait', true ) );
    }
    
    hideBusy(): void
    {
        this.busy.clearValue();
    }
    
    showBusyNoOverlay(): void
    {
        this.busy.setValue( new Busy( 'Please wait', false ) );
    }
    
    changeTheme( theme: string ): void
    {
        if ( ! theme || theme.length === 0 ) theme = 'dark';
        
        AppStateService.Themes.forEach( ( v ) => {
            $( '#GameContainer' ).removeClass( v );
        });
        
        $( '#GameContainer' ).addClass( theme );
        this.theme.setValue( theme );
    }
}
