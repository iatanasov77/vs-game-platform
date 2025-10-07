import { Injectable, Inject, Injector } from '@angular/core';
import { AbstractGameService } from './abstract-game.service';

// NGRX Store
import { loadGameRooms } from '../../+store/game.actions';

// Core Interfaces
import GameState from '_@/GamePlatform/Model/Core/gameState';
import GameCookieDto from '_@/GamePlatform/Model/Core/gameCookieDto';

// CardGame Interfaces
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import BidType from '_@/GamePlatform/Model/CardGame/bidType';
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';

// Action Interfaces
import ActionDto from '../../dto/Actions/actionDto';
import ActionNames from '../../dto/Actions/actionNames';
import ConnectionInfoActionDto from '../../dto/Actions/connectionInfoActionDto';
import CardGameCreatedActionDto from '../../dto/Actions/cardGameCreatedActionDto';
import CardGameEndedActionDto from '../../dto/Actions/cardGameEndedActionDto';
import CardGameRestoreActionDto from '../../dto/Actions/cardGameRestoreActionDto';
import BiddingStartedActionDto from '../../dto/Actions/biddingStartedActionDto';
import BidMadeActionDto from '../../dto/Actions/bidMadeActionDto';
import OpponentBidsActionDto from '../../dto/Actions/opponentBidsActionDto';
import PlayingStartedActionDto from '../../dto/Actions/playingStartedActionDto';
import PlayCardActionDto from '../../dto/Actions/playCardActionDto';

import { Keys } from '../../utils/keys';

@Injectable({
    providedIn: 'root'
})
export class BridgeBeloteService extends AbstractGameService
{
    constructor(
        @Inject( Injector ) private injector: Injector,
    ) {
        super( injector );
    }
    
    connect( gameId: string, playAi: boolean, forGold: boolean ): void
    {
        //alert( 'Called Websocket Connect !!!' );
        if ( this.socket ) {
            this.socket.close();
        }
        
        const currentUrlparams = new URLSearchParams( window.location.search );
        
        let gameCookie  = this.cookieService.get( Keys.gameIdKey );
        let b64Cookie;
        if ( gameCookie ) {
            b64Cookie   = window.btoa( gameCookie );
        }
        
        this.url        = window.gamePlatformSettings.socketGameUrl;
        
        const user      = this.appState.user.getValue();
        const userId    = user ? user.id : '';
        const tree      = this.router.createUrlTree([], {
            queryParams: {
                token: window.gamePlatformSettings.apiVerifySiganature,
                gameCode: window.gamePlatformSettings.gameSlug,
                gameCookie: b64Cookie,
                
                userId: userId,
                gameId: gameId,
                playAi: playAi,
                forGold: forGold
            }
        });
        const url = this.url + this.serializer.serialize( tree );
        
        //alert( url );
        this.socket = new WebSocket( url );
        this.socket.onmessage   = this.onMessage.bind( this );
        this.socket.onerror     = this.onError.bind( this );
        this.socket.onopen      = this.onOpen.bind( this );
        this.socket.onclose     = this.onClose.bind( this );
    }
    
    onOpen(): void
    {
        // console.log('Open', { event });
        const now = new Date();
        const ping = now.getTime() - this.connectTime.getTime();
        
        //console.log( 'User in State', this.appState.user );
        if ( this.appState.user.getValue() ) {
            //this.statusMessageService.setWaitingForConnect();
            this.statusMessageService.setNotGameStarted();
        } else {
            this.statusMessageService.setNotLoggedIn();
            this.appState.hideBusy();
        }
        
        this.appState.myConnection.setValue( { connected: true, pingMs: ping } );
        this.appState.cardGame.clearValue();
        this.appState.dices.clearValue();
    }
    
    // Messages received from server.
    onMessage( message: MessageEvent<string> ): void
    {
        if ( ! message.data.length  ) {
            return;
        }
        
        const action = JSON.parse( message.data ) as ActionDto;
        const game = this.appState.cardGame.getValue();
        //console.log( 'Action', action );
        
        //console.log( 'Game in State', game );
        switch ( action.actionName ) {
            case ActionNames.gameCreated: {
                //console.log( 'WebSocket Action Game Created', action.actionName );
                
                const dto = JSON.parse( message.data ) as CardGameCreatedActionDto;
                console.log( 'WebSocket Action Game Created', dto.game );
                this.appState.myPosition.setValue( dto.myPosition );
                this.appState.cardGame.setValue( dto.game );
                
                const cookie: GameCookieDto = {
                    id: dto.game.id,
                    game: window.gamePlatformSettings.gameSlug,
                    position: dto.myPosition,
                    roomSelected: false
                };
                this.cookieService.deleteAll( Keys.gameIdKey );
                // console.log('Settings cookie', cookie);
                this.cookieService.set( Keys.gameIdKey, JSON.stringify( cookie ), 2 );
                //this.statusMessageService.setTextMessage( dto.game );
                
                //this.store.dispatch( loadGameRooms( { gameSlug: window.gamePlatformSettings.gameSlug } ) );
                
                //this.appState.moveTimer.setValue( dto.game.thinkTime );
                this.sound.fadeIntro();
                this.startTimer();
                
                break;
            }
            case ActionNames.biddingStarted: {
                const biddingStartedAction = JSON.parse( message.data ) as BiddingStartedActionDto;
                console.log( 'Bidding Started Action' + new Date().toLocaleTimeString(), biddingStartedAction );
                
                this.appState.playerCards.setValue( biddingStartedAction.playerCards );
                const cGame = {
                    ...game,
                    validBids: biddingStartedAction.validBids,
                    currentPlayer: biddingStartedAction.firstToBid,
                    playState: GameState.bidding
                };
                
                this.appState.cardGame.setValue( cGame );
                this.statusMessageService.setTextMessage( cGame );
                
                this.appState.moveTimer.setValue( biddingStartedAction.timer );
                this.appState.opponentDone.setValue( true );
                
                break;
            }
            case ActionNames.bidMade: {
                //console.log( 'WebSocket Action Moves Made', action.actionName );
                
                // This action is only sent to server.
                break;
            }
            case ActionNames.opponentBids: {
                //alert( 'WebSocket Action Opponent Move' );
                
                const action = JSON.parse( message.data ) as OpponentBidsActionDto;
                console.log( 'WebSocket Action Opponent Bids', action );
                
                this.doBid( action.bid );
                
                const cGame = {
                    ...game,
                    currentPlayer: action.nextPlayer,
                    playState: action.playState
                };
                // console.log( 'Game After Action Opponent Bids', cGame );
                this.appState.cardGame.setValue( cGame );
                
                break;
            }
            case ActionNames.playingStarted: {
                const playingStartedAction = JSON.parse( message.data ) as PlayingStartedActionDto;
                console.log( 'Playing Started Action' + new Date().toLocaleTimeString(), playingStartedAction );
                
                this.appState.playerCards.setValue( playingStartedAction.playerCards );
                const cGame = {
                    ...game,
                    contract: playingStartedAction.contract,
                    validBids: [],
                    validCards: playingStartedAction.validCards,
                    currentPlayer: playingStartedAction.firstToPlay,
                    playState: GameState.playing
                };
                
                this.appState.cardGame.setValue( cGame );
                this.statusMessageService.setTextMessage( cGame );
                
                this.appState.moveTimer.setValue( playingStartedAction.timer );
                this.appState.opponentDone.setValue( true );
                
                break;
            }
            case ActionNames.gameEnded: {
                //console.log( 'WebSocket Action Game Ended', action.actionName );
                
                const endedAction = JSON.parse( message.data ) as CardGameEndedActionDto;
                //console.log( 'game ended', endedAction.game.winner );
                //console.log( 'WebSocket Action Game Ended', endedAction.game );
                this.appState.cardGame.setValue({
                    ...endedAction.game,
                    playState: GameState.ended
                });
                /*
                this.statusMessageService.setGameEnded(
                    endedAction.game,
                    endedAction.newScore
                );
                this.appState.moveTimer.setValue( 0 );
                */
                break;
            }
            case ActionNames.connectionInfo: {
                //console.log( 'WebSocket Action Connection Info' ); // , action.actionName
                
                const action = JSON.parse( message.data ) as ConnectionInfoActionDto;
                if ( ! action.connection.connected ) {
                    //console.log( 'Opponent disconnected' );
                    this.statusMessageService.setOpponentConnectionLost();
                }
                const cnn = this.appState.opponentConnection.getValue();
                this.appState.opponentConnection.setValue({
                    ...cnn,
                    connected: action.connection.connected
                });
                
                break;
            }
            case ActionNames.gameRestore: {
                //console.log( 'WebSocket Action Game Restore', action.actionName );
                
                const dto = JSON.parse( message.data ) as CardGameRestoreActionDto;
                //console.log( 'WebSocket Action Game Restore', dto );
                
                this.appState.myPosition.setValue( dto.position );
                this.appState.cardGame.setValue( dto.game );
                // this.appState.dices.setValue( dto.dices );
                // this.appState.moveTimer.setValue( dto.game.thinkTime );
                // this.statusMessageService.setTextMessage( dto.game );
                this.startTimer();
                
                break;
            }
            case ActionNames.serverWasTerminated: {
                this.cookieService.deleteAll( Keys.gameIdKey );
                
                break;
            }
            
            default:
                throw new Error( `Action not implemented ${action.actionName}` );
        }
    }
    
    doBid( bid: BidDto ): void
    {
        const playerPosition = bid.Player
        const playerBids = this.appState.playerBids.getValue();
        
        this.appState.playerBids.setValue({
            ...playerBids,
            [playerPosition]: bid
        });
        
        //console.log( 'Player Do Bid', bid );
        //console.log( 'After Player Do Bid', this.appState.playerBids.getValue() );
    }
    
    sendBid( bid: BidDto ): void
    {
        //console.log( 'Player Send Bid', bid );
        const game = this.appState.cardGame.getValue();
        
        const myBidAction: BidMadeActionDto = {
            actionName: ActionNames.bidMade,
            bid: { ...bid, NextBids: [] }
        };
        this.sendMessage( JSON.stringify( myBidAction ) );
        
        const opponentBidAction: OpponentBidsActionDto = {
            actionName: ActionNames.opponentBids,
            bid: { ...bid, NextBids: [] },
            
            nextPlayer: game.currentPlayer,
            playState: game.playState
        };
        this.sendMessage( JSON.stringify( opponentBidAction ) );
    }
}
