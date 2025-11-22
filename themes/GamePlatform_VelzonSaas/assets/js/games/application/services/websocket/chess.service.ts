import { Injectable, Inject, Injector } from '@angular/core';
import { AbstractGameService } from './abstract-game.service';

// NGRX Store
import { loadGameRooms } from '../../+store/game.actions';

// Core Interfaces
import GameCookieDto from '_@/GamePlatform/Model/Core/gameCookieDto';
import GameState from '_@/GamePlatform/Model/Core/gameState';

// BoardGame Interfaces
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import ChessMoveDto from '_@/GamePlatform/Model/BoardGame/chessMoveDto';
import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';

// Action Interfaces
import ActionDto from '../../dto/Actions/actionDto';
import ActionNames from '../../dto/Actions/actionNames';
import DoublingActionDto from '../../dto/Actions/doublingActionDto';
import HintMovesActionDto from '../../dto/Actions/hintMovesActionDto';
import BoardGameCreatedActionDto from '../../dto/Actions/boardGameCreatedActionDto';
import ChessGameStartedActionDto from '../../dto/Actions/chessGameStartedActionDto';
import BoardGameEndedActionDto from '../../dto/Actions/boardGameEndedActionDto';
import ChessMoveMadeActionDto from '../../dto/Actions/chessMoveMadeActionDto';
import ChessOpponentMoveActionDto from '../../dto/Actions/chessOpponentMoveActionDto';
import UndoActionDto from '../../dto/Actions/undoActionDto';
import ConnectionInfoActionDto from '../../dto/Actions/connectionInfoActionDto';
import BoardGameRestoreActionDto from '../../dto/Actions/boardGameRestoreActionDto';

import { Keys } from '../../utils/keys';

@Injectable({
    providedIn: 'root'
})
export class ChessService extends AbstractGameService
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
        this.appState.boardGame.clearValue();
    }
    
    // Messages received from server.
    onMessage( message: MessageEvent<string> ): void
    {
        if ( ! message.data.length  ) {
            return;
        }
        
        const action = JSON.parse( message.data ) as ActionDto;
        const game = this.appState.boardGame.getValue();
        //console.log( 'Action', action );
        
        //console.log( 'Game in State', game );
        switch ( action.actionName ) {
            case ActionNames.gameCreated: {
                //console.log( 'WebSocket Action Game Created', action.actionName );
                
                const dto = JSON.parse( message.data ) as BoardGameCreatedActionDto;
                console.log( 'WebSocket Action Game Created', dto );
                this.appState.myColor.setValue( dto.myColor );
                this.appState.boardGame.setValue( dto.game );
                
                const cookie: GameCookieDto = {
                    id: dto.game.id,
                    game: window.gamePlatformSettings.gameSlug,
                    color: dto.myColor,
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
            case ActionNames.chessGameStarted: {
                const action = JSON.parse( message.data ) as ChessGameStartedActionDto;
                console.log( 'WebSocket Action Chess Game Started', action );
                
                this.appState.boardGame.setValue({
                    ...game,
                    currentPlayer: action.game.currentPlayer
                });
                
                this.appState.moveTimer.setValue( action.moveTimer );
                
                break;
            }
            case ActionNames.chessMoveMade: {
                console.log( 'WebSocket Action Move Made', action.actionName );
                
                // This action is only sent to server.
                break;
            }
            
            case ActionNames.gameEnded: {
                //console.log( 'WebSocket Action Game Ended', action.actionName );
                
                const endedAction = JSON.parse( message.data ) as BoardGameEndedActionDto;
                //console.log( 'game ended', endedAction.game.winner );
                //console.log( 'WebSocket Action Game Ended', endedAction.game );
                this.appState.boardGame.setValue({
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
                //console.log( 'WebSocket Action Requested Doubling' ); // , action.actionName
                
                // Opponent has requested
                const action = JSON.parse( message.data ) as DoublingActionDto;
                this.appState.moveTimer.setValue( action.moveTimer );
                
                this.appState.boardGame.setValue({
                    ...game,
                    playState: GameState.requestedDoubling,
                    currentPlayer: this.appState.myColor.getValue()
                });
                this.statusMessageService.setDoublingRequested();
                
                break;
            }
            case ActionNames.acceptedDoubling: {
                //console.log( 'WebSocket Action Accepted Doubling' ); // , action.actionName
                
                const action = JSON.parse( message.data ) as DoublingActionDto;
                this.appState.moveTimer.setValue( action.moveTimer );
                // Opponent has accepted
                this.appState.boardGame.setValue({
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
            case ActionNames.chessOpponentMove: {
                const action = JSON.parse( message.data ) as ChessOpponentMoveActionDto;
                console.log( 'WebSocket Action Opponent Move ', action );
                
                this.doMove( action.move );
                
                break;
            }
            case ActionNames.undoMove: {
                //console.log( 'WebSocket Action Undo Move', action.actionName );
                
                this.undoMove();
                
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
                
                const dto = JSON.parse( message.data ) as BoardGameRestoreActionDto;
                //console.log( 'WebSocket Action Game Restore', dto );
                
                this.appState.myColor.setValue( dto.color );
                this.appState.boardGame.setValue( dto.game );
                this.appState.moveTimer.setValue( dto.game.thinkTime );
                this.statusMessageService.setTextMessage( dto.game );
                this.startTimer();
                
                break;
            }
            case ActionNames.hintMoves: {
                //console.log( 'WebSocket Action Hint Moves', action.actionName );
                
                const dto = JSON.parse( message.data ) as HintMovesActionDto;
                
                dto.moves.forEach( ( hint ) => {
                    const clone = [...this.appState.moveAnimations.getValue()];
                    // console.log('pushing next animation');
                    clone.push( hint );
                    this.appState.moveAnimations.setValue( clone );
                });
                
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
    
    override resetGame(): void
    {
        super.resetGame();
        
        this.chesUserMoves = [];
    }
    
    /*
    doOpponentMove( move: ChessMoveDto ): void
    {
        const game = this.appState.boardGame.getValue();
        const gameClone = JSON.parse( JSON.stringify( game ) ) as BoardGameDto;
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
        
        this.appState.boardGame.setValue( gameClone );
    }
    */
    
    doMove( move: ChessMoveDto ): void
    {
        //console.log( 'Chess Moee', move );
        this.chesUserMoves.push( { ...move, nextMoves: [] } ); // server does not need to know nextMoves.
        const prevGame = this.appState.boardGame.getValue();
        this.gameHistory.push( prevGame );
        
        const gameClone = JSON.parse( JSON.stringify( prevGame ) ) as BoardGameDto;
        gameClone.currentPlayer = move.color;
        this.appState.boardGame.setValue( gameClone );
        
        const myColor = this.appState.myColor.getValue();
        const oponent = myColor === PlayerColor.black ? PlayerColor.white : PlayerColor.black
        if ( move.color === oponent ) {
            this.appState.chessOpponentMove.setValue( move );
        }
        
        //console.log( 'Do Move', this.chesUserMoves );
    }
    
    undoMove(): void
    {
        if ( this.gameHistory.length < 1 ) {
            return;
        }
        /*
        const move = this.chesUserMoves.pop();
        if ( ! move ) {
            return;
        }
        const game = this.gameHistory.pop() as BoardGameDto;
        this.appState.boardGame.setValue( game );
        
        const clone = [...this.appState.moveAnimations.getValue()];
        // console.log('pushing next animation');
        clone.push( { ...move, from: move.to, to: move.from } );
        this.appState.moveAnimations.setValue( clone );
        */
    }
  
    sendMove( move: ChessMoveDto ): void
    {
        const myMovePieceAction: ChessMoveMadeActionDto = {
            actionName: ActionNames.chessMoveMade,
            move: { ...move, nextMoves: [], animate: true }
        };
        this.sendMessage( JSON.stringify( myMovePieceAction ) );
        
        // removing next moves to decrease bytes.
        const opponentMovePieceAction: ChessOpponentMoveActionDto = {
            actionName: ActionNames.chessOpponentMove,
            move: { ...move, nextMoves: [], animate: true },
            myColor: this.appState.myColor.getValue()
        };
        this.sendMessage( JSON.stringify( opponentMovePieceAction ) );
    }
    
    shiftMoveAnimationsQueue(): void
    {
        // console.log( 'shifting animation queue' );
        const clone = [...this.appState.moveAnimations.getValue()];
        clone.shift();
        this.appState.moveAnimations.setValue( clone );
    }
    
    //This is when this player accepts a doubling.
    acceptDoubling(): void
    {
        const action: DoublingActionDto = {
            actionName: ActionNames.acceptedDoubling,
            moveTimer: 0 // Set on the server
        };
        const game = this.appState.boardGame.getValue();
        this.appState.boardGame.setValue({
            ...game,
            playState: GameState.playing,
            goldMultiplier: game.goldMultiplier * 2,
            lastDoubler: this.appState.getOtherPlayer(),
            currentPlayer: this.appState.getOtherPlayer(),
            whitePlayer: {
                ...game.whitePlayer,
                gold: game.whitePlayer.gold - game.stake / 2
            },
            blackPlayer: {
                ...game.blackPlayer,
                gold: game.blackPlayer.gold - game.stake / 2
            },
            stake: game.stake * 2
        });
        
        // TODO: The client countdown is currently only a constant on the backend.
        // What is the best design here?
        this.appState.moveTimer.setValue( 40 );
        this.sendMessage( JSON.stringify( action ) );
        this.statusMessageService.setTextMessage( this.appState.boardGame.getValue() );
    }
    
    //This player requests doubling.
    requestDoubling(): void
    {
        const game = this.appState.boardGame.getValue();
        const otherPlyr = this.appState.getOtherPlayer();
        this.appState.boardGame.setValue({
            ...game,
            playState: GameState.requestedDoubling,
            currentPlayer: otherPlyr
        });
        
        const action: DoublingActionDto = {
            actionName: ActionNames.requestedDoubling,
            moveTimer: 0 // set on the server
        };
        
        // TODO: The client countdown is currently only a constant on the backend.
        // What is the best design here? Where to store the constant? One extra server message for this case?
        this.appState.moveTimer.setValue( 40 );
        this.sendMessage( JSON.stringify( action ) );
        this.statusMessageService.setWaitingForDoubleResponse();
    }
}
