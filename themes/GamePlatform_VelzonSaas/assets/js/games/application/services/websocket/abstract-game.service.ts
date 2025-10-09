import { Injector } from '@angular/core';

// Services
import { CookieService } from 'ngx-cookie-service';
import { Router, UrlSerializer } from '@angular/router';
import { StatusMessageService } from '../status-message.service';
import { SoundService } from '../sound.service';
import { AppStateService } from '../../state/app-state.service';
import { QueryParamsService } from '../../state/query-params.service';
import { GameService } from '../game.service';

// NGRX Store
import { Store } from '@ngrx/store';
import { selectGameRoom } from '../../+store/game.actions';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';

// Core Interfaces
import GameState from '_@/GamePlatform/Model/Core/gameState';
import GameDto from '_@/GamePlatform/Model/Core/gameDto';
import GameCookieDto from '_@/GamePlatform/Model/Core/gameCookieDto';

// Board Interfaces
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';

// Action Interfaces
import ActionDto from '../../dto/Actions/actionDto';
import ActionNames from '../../dto/Actions/actionNames';
import MovesMadeActionDto from '../../dto/Actions/movesMadeActionDto';
import OpponentMoveActionDto from '../../dto/Actions/opponentMoveActionDto';
import UndoActionDto from '../../dto/Actions/undoActionDto';
import StartGamePlayActionDto from '../../dto/Actions/startGamePlayActionDto';

// Game Variants
import { GameVariant } from "../../game.variant";

// Unused Actions but part of the TypeScript compilation
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
    protected queryParamsService: QueryParamsService;
    protected gameService: GameService;
    protected store: Store;
        
    socket: WebSocket | undefined;
    url: string = '';
    
    userMoves: MoveDto[] = [];
    gameHistory: GameDto[] = [];
    dicesHistory: DiceDto[][] = [];
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
        this.queryParamsService = injector.get( QueryParamsService );
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
    
    abstract connect( gameId: string, playAi: boolean, forGold: boolean ): void;
    abstract onOpen(): void;
    
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
        //this.statusMessageService.setMyConnectionLost( event.reason );
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
    
    acceptInvite( inviteId: string ): void
    {
        const currentUrlparams = new URLSearchParams( window.location.search );
        let variant = currentUrlparams.get( 'variant' );
        if ( variant == null ) {
            variant = GameVariant.BACKGAMMON_NORMAL;
        }
        
        this.queryParamsService.variant.setValue( variant );
        this.queryParamsService.gameId.setValue( inviteId );
        this.queryParamsService.inviteId.clearValue();
        this.queryParamsService.playAi.clearValue();
        this.queryParamsService.forGold.clearValue();
        
        const urlTree = this.router.createUrlTree([], {
            queryParams: { variant: variant, gameId: inviteId },
            queryParamsHandling: "merge",
            preserveFragment: true
        });
        this.router.navigateByUrl( urlTree );
    }
}
