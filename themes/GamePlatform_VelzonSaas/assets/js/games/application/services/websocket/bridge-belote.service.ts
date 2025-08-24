import { Injectable, Inject, Injector } from '@angular/core';
import { AbstractGameService } from './abstract-game.service';

// NGRX Store
import { loadGameRooms } from '../../+store/game.actions';

// BoardGame Interfaces
import CheckerDto from '_@/GamePlatform/Model/BoardGame/checkerDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';
import GameState from '_@/GamePlatform/Model/BoardGame/gameState';

// CardGame Interfaces
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

// Action Interfaces
import ActionDto from '../../dto/Actions/actionDto';
import ActionNames from '../../dto/Actions/actionNames';
import GameCreatedActionDto from '../../dto/Actions/gameCreatedActionDto';
import GameEndedActionDto from '../../dto/Actions/gameEndedActionDto';
import ConnectionInfoActionDto from '../../dto/Actions/connectionInfoActionDto';
import GameRestoreActionDto from '../../dto/Actions/gameRestoreActionDto';

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
    
    // Messages received from server.
    onMessage( message: MessageEvent<string> ): void
    {
        if ( ! message.data.length  ) {
            return;
        }
        
        const action = JSON.parse( message.data ) as ActionDto;
        const game = this.appState.game.getValue();
        //console.log( 'Action', action );
        
        //console.log( 'Game in State', game );
        switch ( action.actionName ) {
            case ActionNames.gameCreated: {
                //console.log( 'WebSocket Action Game Created', action.actionName );
                
                const dto = JSON.parse( message.data ) as GameCreatedActionDto;
                console.log( 'WebSocket Action Game Created', dto.game );
                this.appState.myColor.setValue( dto.myColor );
                this.appState.game.setValue( dto.game );
                
                const cookie: GameCookieDto = {
                    id: dto.game.id,
                    game: window.gamePlatformSettings.gameSlug,
                    color: dto.myColor,
                    position: PlayerPosition.neither,
                    roomSelected: false
                };
                this.cookieService.deleteAll( Keys.gameIdKey );
                // console.log('Settings cookie', cookie);
                this.cookieService.set( Keys.gameIdKey, JSON.stringify( cookie ), 2 );
                this.statusMessageService.setTextMessage( dto.game );
                
                this.store.dispatch( loadGameRooms( { gameSlug: window.gamePlatformSettings.gameSlug } ) );
                
                this.appState.moveTimer.setValue( dto.game.thinkTime );
                this.sound.fadeIntro();
                this.startTimer();
                
                break;
            }
            case ActionNames.gameEnded: {
                //console.log( 'WebSocket Action Game Ended', action.actionName );
                
                const endedAction = JSON.parse( message.data ) as GameEndedActionDto;
                //console.log( 'game ended', endedAction.game.winner );
                //console.log( 'WebSocket Action Game Ended', endedAction.game );
                this.appState.game.setValue({
                    ...endedAction.game,
                    playState: GameState.ended
                });
                this.statusMessageService.setGameEnded(
                    endedAction.game,
                    endedAction.newScore
                );
                this.appState.moveTimer.setValue( 0 );
                
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
                
                const dto = JSON.parse( message.data ) as GameRestoreActionDto;
                //console.log( 'WebSocket Action Game Restore', dto );
                
                this.appState.myColor.setValue( dto.color );
                this.appState.game.setValue( dto.game );
                this.appState.dices.setValue( dto.dices );
                this.appState.moveTimer.setValue( dto.game.thinkTime );
                this.statusMessageService.setTextMessage( dto.game );
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
