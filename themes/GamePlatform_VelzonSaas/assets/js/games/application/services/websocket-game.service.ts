import { Injectable, Inject } from '@angular/core';

import { CookieService } from 'ngx-cookie-service';
import { StatusMessageService } from './status-message.service';
import { SoundService } from './sound.service';
import { AppStateService } from '../state/app-state.service';
import { GameService } from './game.service';

// Board Interfaces
import CheckerDto from '_@/GamePlatform/Model/BoardGame/checkerDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';
import GameState from '_@/GamePlatform/Model/BoardGame/gameState';

// Action Interfaces
import ActionDto from '../dto/Actions/actionDto';
import DoublingActionDto from '../dto/Actions/doublingActionDto';
import HintMovesActionDto from '../dto/Actions/hintMovesActionDto';
import ActionNames from '../dto/Actions/actionNames';
import DicesRolledActionDto from '../dto/Actions/dicesRolledActionDto';
import GameCreatedActionDto from '../dto/Actions/gameCreatedActionDto';
import GameEndedActionDto from '../dto/Actions/gameEndedActionDto';
import MovesMadeActionDto from '../dto/Actions/movesMadeActionDto';
import OpponentMoveActionDto from '../dto/Actions/opponentMoveActionDto';
import UndoActionDto from '../dto/Actions/undoActionDto';
import ConnectionInfoActionDto from '../dto/Actions/connectionInfoActionDto';
import GameRestoreActionDto from '../dto/Actions/gameRestoreActionDto';

import { Keys } from '../utils/keys';
import { MessageLevel, StatusMessage } from '../utils/status-message';

declare global {
    interface Window {
        gamePlatformSettings: any
    }
}

@Injectable({
    providedIn: 'root'
})
export class WebsocketGameService
{
    socket: WebSocket | undefined;
    url: string = '';
    
    userMoves: MoveDto[] = [];
    gameHistory: GameDto[] = [];
    dicesHistory: DiceDto[][] = [];
    connectTime = new Date();
    
    timerStarted = false;
    timerId: any;
  
    constructor(
        @Inject( CookieService ) private cookieService: CookieService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
        @Inject( SoundService ) private sound: SoundService,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( GameService ) private gameService: GameService,
    ) { }
    
    connect( gameId: string, playAi: boolean, forGold: boolean ): void
    {
        if ( this.socket ) {
            this.socket.close();
        }
        
        const url   = window.gamePlatformSettings.socketGameUrl +
                        '?gameCode=backgammon' +
                        '&token=' + window.gamePlatformSettings.apiVerifySiganature;
                        
        this.url    = url;
        this.socket = new WebSocket( this.url );
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
            //this.statusMessageService.setWaitingForConnect();
            this.statusMessageService.setNotGameStarted();
        } else {
            this.statusMessageService.setNotLoggedIn();
        }
        this.appState.myConnection.setValue({ connected: true, pingMs: ping });
        
        this.appState.game.clearValue();
        this.appState.dices.clearValue();
    }
    
    onError( event: Event ): void
    {
        console.error( 'Error', { event } );
        const cnn = this.appState.myConnection.getValue();
        this.appState.myConnection.setValue({ ...cnn, connected: false });
        this.statusMessageService.setMyConnectionLost( '' );
    }
    
    onClose(): void
    {
        // console.log( 'Close', { event } );
        const cnn = this.appState.myConnection.getValue();
        this.appState.myConnection.setValue({ ...cnn, connected: false });
        // this.statusMessageService.setMyConnectionLost( event.reason );
    }
    
    // Messages received from server.
    onMessage( message: MessageEvent<string> ): void
    {
        console.log( 'Message', message );
        alert( 'Message: ' + message );
        
        if ( ! message || typeof message !== 'object'  ) {
            return;
        }
        
        const action = JSON.parse( message.data ) as ActionDto;
        const game = this.appState.game.getValue();
        console.log( 'Action', action );
        
        switch ( action.actionName ) {
            case ActionNames.gameCreated: {
                const dto = JSON.parse( message.data ) as GameCreatedActionDto;
                this.appState.myColor.setValue( dto.myColor );
                this.appState.game.setValue( dto.game );
                
                const cookie: GameCookieDto = { id: dto.game.id, color: dto.myColor };
                this.cookieService.deleteAll( Keys.gameIdKey );
                // console.log('Settings cookie', cookie);
                this.cookieService.set( Keys.gameIdKey, JSON.stringify( cookie ), 2 );
                this.statusMessageService.setTextMessage( dto.game );
                this.appState.moveTimer.setValue( dto.game.thinkTime );
                this.sound.fadeIntro();
                this.startTimer();
                break;
            }
            case ActionNames.dicesRolled: {
                const dicesAction = JSON.parse( message.data ) as DicesRolledActionDto;
                this.appState.dices.setValue( dicesAction.dices );
                const cGame = {
                    ...game,
                    validMoves: dicesAction.validMoves,
                    currentPlayer: dicesAction.playerToMove,
                    playState: GameState.playing
                };
                // console.log(dicesAction.validMoves);
                this.appState.game.setValue( cGame );
                this.statusMessageService.setTextMessage( cGame );
                this.appState.moveTimer.setValue( dicesAction.moveTimer );
                this.appState.opponentDone.setValue( true );
                break;
            }
            case ActionNames.movesMade: {
                // This action is only sent to server.
                break;
            }
            case ActionNames.gameEnded: {
                const endedAction = JSON.parse( message.data ) as GameEndedActionDto;
                // console.log( 'game ended', endedAction.game.winner );
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
            case ActionNames.requestedDoubling: {
                // Opponent has requested
                const action = JSON.parse( message.data ) as DoublingActionDto;
                this.appState.moveTimer.setValue( action.moveTimer );
                
                this.appState.game.setValue({
                    ...game,
                    playState: GameState.requestedDoubling,
                    currentPlayer: this.appState.myColor.getValue()
                });
                this.statusMessageService.setDoublingRequested();
                break;
            }
            case ActionNames.acceptedDoubling: {
                const action = JSON.parse( message.data ) as DoublingActionDto;
                this.appState.moveTimer.setValue( action.moveTimer );
                // Opponent has accepted
                this.appState.game.setValue({
                    ...game,
                    playState: GameState.playing,
                    goldMultiplier: game.goldMultiplier * 2,
                    lastDoubler: this.appState.myColor.getValue(),
                    currentPlayer: this.appState.myColor.getValue(),
                    stake: game.stake * 2,
                    whitePlayer: {
                        ...game.whitePlayer,
                        gold: game.whitePlayer.gold - game.stake / 2
                    },
                    blackPlayer: {
                        ...game.blackPlayer,
                        gold: game.blackPlayer.gold - game.stake / 2
                    }
                });
                this.sound.playCoin();
                this.statusMessageService.setDoublingAccepted();
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
            case ActionNames.rolled: {
                // this is just to fire the changed event. The value is not important.
                this.appState.rolled.setValue( true );
                break;
            }
            case ActionNames.connectionInfo: {
                const action = JSON.parse( message.data ) as ConnectionInfoActionDto;
                if ( ! action.connection.connected ) {
                    console.log( 'Opponent disconnected' );
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
                const dto = JSON.parse( message.data ) as GameRestoreActionDto;
                this.appState.myColor.setValue( dto.color );
                this.appState.game.setValue( dto.game );
                this.appState.dices.setValue( dto.dices );
                this.appState.moveTimer.setValue( dto.game.thinkTime );
                this.statusMessageService.setTextMessage( dto.game );
                this.startTimer();
                break;
            }
            case ActionNames.hintMoves: {
                const dto = JSON.parse( message.data ) as HintMovesActionDto;
                
                dto.moves.forEach( ( hint ) => {
                    const clone = [...this.appState.moveAnimations.getValue()];
                    // console.log('pushing next animation');
                    clone.push( hint );
                    this.appState.moveAnimations.setValue( clone );
                });
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
    
    doOpponentMove( move: MoveDto ): void
    {
        const game = this.appState.game.getValue();
        const gameClone = JSON.parse( JSON.stringify( game ) ) as GameDto;
        const isWhite = move.color === PlayerColor.white;
        const from = isWhite ? 25 - move.from : move.from;
        const to = isWhite ? 25 - move.to : move.to;
        //const checker = <CheckerDto>gameClone.points[from].checkers.pop();
        
        // hitting opponent checker
        const hit = gameClone.points[to].checkers.find(
            ( c ) => c.color !== move.color
        );
        if ( hit ) {
            gameClone.points[to].checkers.pop();
            const barIdx = isWhite ? 0 : 25;
            gameClone.points[barIdx].checkers.push( hit );
        }
        
        //gameClone.points[to].checkers.push( checker );
        
        this.appState.game.setValue( gameClone );
    }
    
    doMove( move: MoveDto ): void
    {
        this.userMoves.push( { ...move, nextMoves: [] } ); // server does not need to know nextMoves.
        const prevGame = this.appState.game.getValue();
        this.gameHistory.push( prevGame );
        
        const gameClone = JSON.parse( JSON.stringify( prevGame ) ) as GameDto;
        gameClone.validMoves = move.nextMoves;
        const isWhite = move.color === PlayerColor.white;
        const from = isWhite ? 25 - move.from : move.from;
        const to = isWhite ? 25 - move.to : move.to;
        
        // remove moved checker
//         const checker = <CheckerDto>(
//             gameClone.points[from].checkers.find((c) => c.color === move.color)
//         );
//         const index = gameClone.points[from].checkers.indexOf( checker );
//         gameClone.points[from].checkers.splice( index, 1 );
        
        if ( move.color == PlayerColor.black ) {
            gameClone.blackPlayer.pointsLeft -= move.to - move.from;
        } else {
            gameClone.whitePlayer.pointsLeft -= move.to - move.from;
        }
        // hitting opponent checker
        const hit = gameClone.points[to].checkers.find(
            ( c ) => c.color !== move.color
        );
        
        if ( hit ) {
            if ( move.to < 25 ) {
                this.sound.playCheckerWood();
            }
            
            const hitIdx = gameClone.points[to].checkers.indexOf( hit );
            gameClone.points[to].checkers.splice( hitIdx, 1 );
            const barIdx = isWhite ? 0 : 25;
            gameClone.points[barIdx].checkers.push( hit );
            
            if ( move.color == PlayerColor.black ) {
                gameClone.whitePlayer.pointsLeft += 25 - move.to;
            } else {
                gameClone.blackPlayer.pointsLeft += 25 - move.to;
            }
        }
        
        //push checker to new point
//         gameClone.points[to].checkers.push( checker );
        this.appState.game.setValue( gameClone );
        
        const dices = this.appState.dices.getValue();
        this.dicesHistory.push( dices );
        
        const diceClone = JSON.parse( JSON.stringify( dices ) ) as DiceDto[];
        
        // Find a dice with equal value as the move length
        // or if bearing off equal or larger
        let diceIdx = diceClone.findIndex(
            ( d ) => !d.used && d.value === move.to - move.from
        );
        
        if ( diceIdx < 0 ) {
            diceIdx = diceClone.findIndex(
                ( d ) => move.to === 25 && move.to - move.from <= d.value
            );
        }
        const dice = diceClone[diceIdx];
        dice.used = true;
        this.appState.dices.setValue( diceClone );
        if ( move.animate ) {
            const clone = [...this.appState.moveAnimations.getValue()];
            // console.log('pushing next animation');
            clone.push( move );
            this.appState.moveAnimations.setValue( clone );
        }
    }
    
    undoMove(): void
    {
        if ( this.gameHistory.length < 1 ) {
            return;
        }
        const move = this.userMoves.pop();
        if ( ! move ) {
            return;
        }
        const game = this.gameHistory.pop() as GameDto;
        this.appState.game.setValue( game );
        
        const dices = this.dicesHistory.pop() as DiceDto[];
        this.appState.dices.setValue( dices );
        
        const clone = [...this.appState.moveAnimations.getValue()];
        // console.log('pushing next animation');
        clone.push( { ...move, from: move.to, to: move.from } );
        this.appState.moveAnimations.setValue( clone );
    }
      
    sendMessage( message: string ): void
    {
      if ( this.socket && this.socket.readyState === this.socket.OPEN ) {
        this.socket.send( message );
      }
    }
  
    sendMoves(): void
    {
        const myColor = this.appState.myColor.getValue();
        // Opponent moves are also stored in userMoves but we cant send them back.
        const action: MovesMadeActionDto = {
            actionName: ActionNames.movesMade,
            moves: this.userMoves.filter( ( m ) => m.color === myColor )
        };
        this.sendMessage( JSON.stringify( action ) );
        
        this.userMoves = [];
        this.dicesHistory = [];
        this.gameHistory = [];
    }
    
    shiftMoveAnimationsQueue(): void
    {
        // console.log( 'shifting animation queue' );
        const clone = [...this.appState.moveAnimations.getValue()];
        clone.shift();
        this.appState.moveAnimations.setValue( clone );
    }
    
    sendMove( move: MoveDto ): void
    {
        // removing next moves to decrease bytes.
        const action: OpponentMoveActionDto = {
            actionName: ActionNames.opponentMove,
            move: { ...move, nextMoves: [], animate: true }
        };
        this.sendMessage( JSON.stringify( action ) );
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
    }
    
    resetGame(): void
    {
        this.cookieService.deleteAll( Keys.gameIdKey );
        this.userMoves = [];
        this.gameHistory = [];
        this.dicesHistory = [];
        this.connectTime = new Date();
    }
}
