import { Injectable, Inject, Injector } from '@angular/core';
import { AbstractGameService } from './abstract-game.service';

// NGRX Store
import { loadGameRooms } from '../../+store/game.actions';

// Core Interfaces
import GameState from '_@/GamePlatform/Model/Core/gameState';
import GameCookieDto from '_@/GamePlatform/Model/Core/gameCookieDto';

// CardGame Interfaces
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';

// Action Interfaces
import ActionDto from '../../dto/Actions/actionDto';
import ActionNames from '../../dto/Actions/actionNames';
import CardGameCreatedActionDto from '../../dto/Actions/cardGameCreatedActionDto';
import CardGameEndedActionDto from '../../dto/Actions/cardGameEndedActionDto';
import ConnectionInfoActionDto from '../../dto/Actions/connectionInfoActionDto';
import CardGameRestoreActionDto from '../../dto/Actions/cardGameRestoreActionDto';

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
}
