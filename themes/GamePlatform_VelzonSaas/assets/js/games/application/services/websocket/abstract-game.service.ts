import { Injector } from '@angular/core';

// Services
import { CookieService } from 'ngx-cookie-service';
import { Router, UrlSerializer } from '@angular/router';
import { StatusMessageService } from '../status-message.service';
import { SoundService } from '../sound.service';
import { AppStateService } from '../../state/app-state.service';
import { GameService } from '../game.service';

// NGRX Store
import { Store } from '@ngrx/store';
import { selectGameRoom } from '../../+store/game.actions';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';

// Board Interfaces
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';
import GameState from '_@/GamePlatform/Model/BoardGame/gameState';

// Action Interfaces
import ActionDto from '../../dto/Actions/actionDto';
import ActionNames from '../../dto/Actions/actionNames';
import MovesMadeActionDto from '../../dto/Actions/movesMadeActionDto';
import OpponentMoveActionDto from '../../dto/Actions/opponentMoveActionDto';
import UndoActionDto from '../../dto/Actions/undoActionDto';
import StartGamePlayActionDto from '../../dto/Actions/startGamePlayActionDto';

// Unused Actions but part of the TypeScript compilation
import GamePlayStartedActionDto from '../../dto/Actions/gamePlayStartedActionDto';
import ServerWasTerminatedActionDto from '../../dto/Actions/serverWasTerminatedActionDto';

import { Keys } from '../../utils/keys';
import { MessageLevel, StatusMessage } from '../../utils/status-message';

declare global {
    interface Window {
        gamePlatformSettings: any
    }
}

export abstract class AbstractGameService
{
    protected cookieService: CookieService;
    protected statusMessageService: StatusMessageService;
    protected router: Router;
    protected serializer: UrlSerializer;
    protected sound: SoundService;
    protected appState: AppStateService;
    protected gameService: GameService;
    protected store: Store;
        
    socket: WebSocket | undefined;
    url: string = '';
    
    gameHistory: GameDto[] = [];
    connectTime = new Date();
    
    timerStarted = false;
    timerId: any;
    
    constructor( injector: Injector )
    {
        this.cookieService = injector.get( CookieService );
        this.statusMessageService = injector.get( StatusMessageService );
        this.router = injector.get( Router );
        this.serializer = injector.get( UrlSerializer );
        this.sound = injector.get( SoundService );
        this.appState = injector.get( AppStateService );
        this.gameService = injector.get( GameService );
        this.store = injector.get( Store );
    
        this.store.subscribe( ( state: any ) => {
            //alert( state.app.main.rooms );
            if ( state.app.main.rooms ) {
                this.selectGameRoomFromCookie( state.app.main.rooms );
            }
        });
    }
    
    selectGameRoomFromCookie( rooms: IGameRoom[] ): void
    {
        //console.log( 'Rooms in Game State: ', rooms );
        let gameCookie  = this.cookieService.get( Keys.gameIdKey );
        if ( gameCookie ) {
            let gameCookieDto   = JSON.parse( gameCookie ) as GameCookieDto;
            //alert( 'Game ID: ' + gameCookieDto.id );
            
            let gameRoom    = rooms.find( ( item: any ) => item?.name === gameCookieDto.id );
            if ( gameRoom && ! gameCookieDto.roomSelected ) {
                //alert( 'Game Room Found From Cookie.' );
                this.store.dispatch( selectGameRoom( { game: gameRoom.game, room: gameRoom } ) );
            }
        }
    }
    
    connect( gameId: string, playAi: boolean, forGold: boolean ): void
    {
        if ( this.socket ) {
            this.socket.close();
        }
        
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
                gameCode: window.gamePlatformSettings.gameSlug,
                token: window.gamePlatformSettings.apiVerifySiganature,
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
        const now = new Date();
        const ping = now.getTime() - this.connectTime.getTime();
        
        //console.log( 'User in State', this.appState.user );
        if ( this.appState.user.getValue() ) {
            this.statusMessageService.setWaitingForConnect();
            //this.statusMessageService.setNotGameStarted();
            //this.appState.hideBusy();
        } else {
            this.statusMessageService.setNotLoggedIn();
            this.appState.hideBusy();
        }
        
        this.appState.myConnection.setValue( { connected: true, pingMs: ping } );
        this.appState.game.clearValue();
        this.appState.dices.clearValue();
    }
    
    onError( event: Event ): void
    {
        console.error( 'Error', { event } );
        const cnn = this.appState.myConnection.getValue();
        this.appState.myConnection.setValue( { ...cnn, connected: false } );
        this.statusMessageService.setMyConnectionLost( '' );
    }
    
    onClose( event: CloseEvent ): void
    {
        //alert( event.code );
        console.log( 'Close', { event } );
        
        // Set Status Message
        const cnn = this.appState.myConnection.getValue();
        this.appState.myConnection.setValue( { ...cnn, connected: false } );
        this.statusMessageService.setMyConnectionLost( event.reason );
    }
    
    abstract onMessage( message: MessageEvent<string> ): void;
    
    startTimer(): void
    {
        if ( this.timerStarted ) {
            return;
        }
        
        this.timerStarted = true;
        this.timerId = setInterval( () => {
            let time = this.appState.moveTimer.getValue();
            time -= 0.25;
            this.appState.moveTimer.setValue( time );
            
            if ( time > 0 && time < 10 ) {
                this.sound.playTick();
            }
      
            if ( time <= 0 ) {
                const currentMes = this.appState.statusMessage.getValue();
                if (
                    this.appState.myTurn() &&
                    currentMes.level !== MessageLevel.warning
                ) {
                    this.statusMessageService.setMoveNow();
                }
            }
        }, 250 );
    }
      
    sendMessage( message: string ): void
    {
        if ( this.socket && this.socket.readyState === this.socket.OPEN ) {
            this.socket.send( message );
        }
    }
    
    sendUndo(): void
    {
        const action: UndoActionDto = {
            actionName: ActionNames.undoMove
        };
        this.sendMessage( JSON.stringify( action ) );
    }

    resignGame(): void
    {
        const action: ActionDto = {
            actionName: ActionNames.resign
        };
        this.sendMessage( JSON.stringify( action ) );
    }
    
    exitGame(): void
    {
        const action: ActionDto = {
            actionName: ActionNames.exitGame
        };
        this.sendMessage( JSON.stringify( action ) );
        clearTimeout( this.timerId );
        this.timerStarted = false;
    }
    
    resetGame(): void
    {
        this.cookieService.deleteAll( Keys.gameIdKey );
        this.gameHistory = [];
        this.connectTime = new Date();
    }
    
    requestHint(): void
    {
        const action: ActionDto = {
            actionName: ActionNames.requestHint
        };
        this.sendMessage( JSON.stringify( action ) );
    }
    
    startGamePlay( game: GameDto, myColor: PlayerColor, playAi: boolean, forGold: boolean ): void
    {
        if ( ! this.socket || this.socket.readyState !== this.socket.OPEN ) {
            this.connect( '', playAi, forGold );
        }
        
        /**
         * Delete Cookie on Every Browser Refresh,
         * May be later this should on DEV Environement Only.
         */
        this.cookieService.deleteAll( Keys.gameIdKey );
        
        const action: StartGamePlayActionDto = {
            actionName: ActionNames.startGamePlay,
            game: game,
            myColor: myColor
        };
        
        this.sendMessage( JSON.stringify( action ) );
    }
}
