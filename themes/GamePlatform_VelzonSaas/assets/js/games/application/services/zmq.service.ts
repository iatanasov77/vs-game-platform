import { Injectable, Inject } from '@angular/core';
import { CookieService } from 'ngx-cookie-service';
import { StatusMessageService } from './status-message.service';

// Board Interfaces
import CheckerDto from '_@/GamePlatform/Model/BoardGame/connectionDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';
import GameState from '_@/GamePlatform/Model/BoardGame/gameState';

// Action Interfaces
import ActionDto from '../interfaces/Actions/actionDto';
import ActionNames from '../interfaces/Actions/actionNames';
import DicesRolledActionDto from '../interfaces/Actions/dicesRolledActionDto';
import GameCreatedActionDto from '../interfaces/Actions/gameCreatedActionDto';
import GameEndedActionDto from '../interfaces/Actions/gameEndedActionDto';
import MovesMadeActionDto from '../interfaces/Actions/movesMadeActionDto';
import OpponentMoveActionDto from '../interfaces/Actions/opponentMoveActionDto';
import UndoActionDto from '../interfaces/Actions/undoActionDto';
import ConnectionInfoActionDto from '../interfaces/Actions/connectionInfoActionDto';
import GameRestoreActionDto from '../interfaces/Actions/gameRestoreActionDto';

import { AppState } from '../state/app-state';
import { Keys } from '../utils/keys';
import { MessageLevel, StatusMessage } from '../utils/status-message';

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

/**
 * Use API Server to Push Messages to Mercure
 */
@Injectable({
    providedIn: 'root'
})
export class ZmqService
{
    socket: any;
    url: string = '';
    
    userMoves: MoveDto[] = [];
    gameHistory: GameDto[] = [];
    dicesHistory: DiceDto[][] = [];
    connectTime = new Date();
    
    timerStarted = false;
  
    constructor(
        @Inject( CookieService ) private cookieService: CookieService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
    ) { }
    
    connect( gameId: string ): void
    {
        this.url = window.gamePlatformSettings.socketPublisherUrl;
        const user = AppState.Singleton.user.getValue();
        const userId = user ? user.id : '';
        //alert( userId );
        
        this.socket = new ab.Session( this.url,
            function() {
                console.log( 'Open', { event } );
                const now = new Date();
                const ping = now.getTime() - this.connectTime.getTime();
                this.statusMessageService.setWaitingForConnect();
                AppState.Singleton.myConnection.setValue( { connected: true, pingMs: ping } );
                AppState.Singleton.game.clearValue();
                AppState.Singleton.dices.clearValue();
            
                this.socket.subscribe( 'game', this.onMessage );
            },
            function() {
                console.log( 'Close', { event } );
                const cnn = AppState.Singleton.myConnection.getValue();
                AppState.Singleton.myConnection.setValue( { ...cnn, connected: false } );
                this.statusMessageService.setMyConnectionLost( event.reason );
            },
            {'skipSubprotocolCheck': true}
        );
    }
    
    // Messages received from server.
    onMessage( topic, message ): void
    {
        const action = JSON.parse( message.data ) as ActionDto;
        // console.log(message.data);
        const game = AppState.Singleton.game.getValue();
        switch ( action.actionName ) {
            case ActionNames.gameCreated: {
                const dto = JSON.parse( message.data ) as GameCreatedActionDto;
                AppState.Singleton.myColor.setValue( dto.myColor );
                AppState.Singleton.game.setValue( dto.game );
                
                const cookie: GameCookieDto = { id: dto.game.id, color: dto.myColor };
                this.cookieService.deleteAll( Keys.gameIdKey );
                // console.log('Settings cookie', cookie);
                this.cookieService.set( Keys.gameIdKey, JSON.stringify( cookie ), 2 );
                this.statusMessageService.setTextMessage( dto.game );
                AppState.Singleton.moveTimer.setValue( dto.game.thinkTime );
                this.startTimer();
                break;
            }
            case ActionNames.dicesRolled: {
                const dicesAction = JSON.parse( message.data ) as DicesRolledActionDto;
                AppState.Singleton.dices.setValue( dicesAction.dices );
                const cGame = {
                    ...game,
                    validMoves: dicesAction.validMoves,
                    currentPlayer: dicesAction.playerToMove,
                    playState: GameState.playing
                };
                // console.log(dicesAction.validMoves);
                AppState.Singleton.game.setValue( cGame );
                this.statusMessageService.setTextMessage( cGame );
                AppState.Singleton.moveTimer.setValue( dicesAction.moveTimer );
                break;
            }
            case ActionNames.movesMade: {
                // This action is only sent to server.
                break;
            }
            case ActionNames.gameEnded: {
                const endedAction = JSON.parse( message.data ) as GameEndedActionDto;
                // console.log( 'game ended', endedAction.game.winner );
                AppState.Singleton.game.setValue( endedAction.game );
                AppState.Singleton.moveTimer.setValue( 0 );
                this.statusMessageService.setGameEnded(
                    endedAction.game,
                    endedAction.newScore
                );
                break;
            }
            case ActionNames.opponentMove: {
                const action = JSON.parse( message.data ) as OpponentMoveActionDto;
                this.doMove( action.move );
                break;
            }
            case ActionNames.undoMove: {
                // const action = JSON.parse( message.data ) as UndoActionDto;
                this.undoMove();
                break;
            }
            case ActionNames.connectionInfo: {
                const action = JSON.parse( message.data ) as ConnectionInfoActionDto;
                if ( ! action.connection.connected ) {
                    console.log( 'Opponent disconnected' );
                    this.statusMessageService.setOpponentConnectionLost();
                }
                const cnn = AppState.Singleton.opponentConnection.getValue();
                AppState.Singleton.opponentConnection.setValue({
                    ...cnn,
                    connected: action.connection.connected
                });
                break;
            }
            case ActionNames.gameRestore: {
                const dto = JSON.parse( message.data ) as GameRestoreActionDto;
                AppState.Singleton.myColor.setValue( dto.color );
                AppState.Singleton.game.setValue( dto.game );
                AppState.Singleton.dices.setValue( dto.dices );
                AppState.Singleton.moveTimer.setValue( dto.game.thinkTime );
                this.statusMessageService.setTextMessage( dto.game );
                this.startTimer();
                break;
            }
            
            default:
                throw new Error( `Action not implemented ${action.actionName}` );
        }
    }
    
    startTimer(): void
    {
        if ( this.timerStarted ) {
            return;
        }
        
        this.timerStarted = true;
        setInterval( () => {
            let time = AppState.Singleton.moveTimer.getValue();
            time--;
            AppState.Singleton.moveTimer.setValue( time );
            if ( time <= 0 ) {
                const currentMes = AppState.Singleton.statusMessage.getValue();
                if (
                    AppState.Singleton.myTurn() &&
                    currentMes.level !== MessageLevel.warning
                ) {
                    const mes = StatusMessage.warning( 'Move now or lose!' );
                    // A few more seconds are given on the server.
                    AppState.Singleton.statusMessage.setValue( mes );
                }
            }
        }, 1000 );
    }
}
