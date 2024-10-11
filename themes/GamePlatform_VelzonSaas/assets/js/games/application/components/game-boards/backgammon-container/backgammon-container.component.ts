import {
    Component,
    OnInit,
    OnDestroy,
    AfterViewInit,
    OnChanges,
    SimpleChanges,
    Inject,
    ElementRef,
    Input,
    ViewChild,
    HostListener
} from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable, Subscription, of } from 'rxjs';
import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import {
    selectGameRoomSuccess,
    startGameSuccess,
    loadGameRooms
} from '../../../+store/game.actions';
import { GameState as MyGameState } from '../../../+store/game.reducers';

import { RequirementsDialogComponent } from '../../shared/requirements-dialog/requirements-dialog.component';
import { SelectGameRoomDialogComponent } from '../select-game-room-dialog/select-game-room-dialog.component';
import { CreateGameRoomDialogComponent } from '../create-game-room-dialog/create-game-room-dialog.component';

// Services
import { AuthService } from '../../../services/auth.service';
import { ZmqGameService } from '../../../services/zmq-game.service';
import { WebsocketGameService } from '../../../services/websocket-game.service';
import { StatusMessageService } from '../../../services/status-message.service';
import { SoundService } from '../../../services/sound.service';

// App State
import { AppStateService } from '../../../state/app-state.service';
import { StatusMessage } from '../../../utils/status-message';
import { Busy } from '../../../state/busy';

// Board Interfaces
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import GameState from '_@/GamePlatform/Model/BoardGame/gameState';

import cssGameString from './backgammon-container.component.scss';
import templateString from './backgammon-container.component.html';

declare var $: any;

/**
 * Forked From: https://www.codeproject.com/Articles/5297405/Online-Backgammon
 * Play Original Game: https://backgammon.azurewebsites.net/
 */
@Component({
    selector: 'app-backgammon-container',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class BackgammonContainerComponent implements OnInit, OnDestroy, AfterViewInit, OnChanges
{
    @Input() isLoggedIn: boolean        = false;
    @Input() hasPlayer: boolean         = false;
    @Input() game: any;
    
    @ViewChild( 'dices' ) dices: ElementRef | undefined;
    @ViewChild( 'messages' ) messages: ElementRef | undefined;
    
    gameDto$: Observable<GameDto>;
    dices$: Observable<DiceDto[]>;
    playerColor$: Observable<PlayerColor>;
    message$: Observable<StatusMessage>;
    timeLeft$: Observable<number>;
    gameSubs: Subscription;
    diceSubs: Subscription;
    
    width = 450;
    height = 450;
    started = false;
    rollButtonClicked = false;
    diceColor: PlayerColor | null = PlayerColor.neither;
    messageCenter = 0;
    flipped = false;
    playAiFlag = false;
    forGodlFlag = false;
    playAiQuestion = false;
  
    rollButtonVisible = false;
    sendVisible = false;
    undoVisible = false;
    dicesVisible = false;
    newVisible = true;
    exitVisible = false;
    dicesDto: DiceDto[] | undefined;
    
    appState?: MyGameState;
    gameStarted: boolean                = false;
    isRoomSelected: boolean             = false;
    gamePlayers: any;
    startedHandle: any;
    
    constructor(
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions,
        @Inject( NgbModal ) private ngbModal: NgbModal,
        
        @Inject( AuthService ) private authService: AuthService,
        @Inject( ZmqGameService ) private zmqService: ZmqGameService,
        @Inject( WebsocketGameService ) private wsService: WebsocketGameService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( SoundService ) private sound: SoundService,
    ) {
        this.gameDto$ = this.appStateService.game.observe();
        this.dices$ = this.appStateService.dices.observe();
        this.diceSubs = this.appStateService.dices.observe().subscribe( this.diceChanged.bind( this ) );
        this.playerColor$ = this.appStateService.myColor.observe();
        this.gameSubs = this.appStateService.game.observe().subscribe( this.gameChanged.bind( this ) );
        this.message$ = this.appStateService.statusMessage.observe();
        this.timeLeft$ = this.appStateService.moveTimer.observe();
        
        // if game page is refreshed, restore user from login cookie
        if ( ! this.appStateService.user.getValue() ) {
            this.authService.repair();
        }
        
        const gameId = 'backgammon';
        const playAi = true;
        const forGold = true;
    
        //this.zmqService.connect( gameId, playAi, forGold );
        this.wsService.connect( gameId, playAi, forGold );
    }
    
    ngOnInit(): void
    {
        this.store.subscribe( ( state: any ) => {
            console.log( state.app.main );
            this.appState   = state.app.main;
            
            if ( state.app.main.gamePlay ) {
                this.gameStarted    = true;
            }
        });
        
        this.actions$.pipe( ofType( selectGameRoomSuccess ) ).subscribe( () => {
            this.isRoomSelected = true;
        });
        
        this.actions$.pipe( ofType( startGameSuccess ) ).subscribe( () => {
            this.store.dispatch( loadGameRooms() );
            this.game.startGame();
        });
    }
    
    ngOnDestroy(): void
    {
        this.gameSubs.unsubscribe();
        this.diceSubs.unsubscribe();
    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'isLoggedIn':
                    this.isLoggedIn = changedProp.currentValue;
                    break;
                case 'hasPlayer':
                    this.hasPlayer = changedProp.currentValue;
                    break;
                case 'game':
                    this.game = changedProp.currentValue;
                    break;
            }
        }
    }
    
    private waitForOpponent()
    {
        this.sound.playPianoIntro();
        this.startedHandle = setTimeout( () => {
            if ( ! this.started ) {
                this.playAiQuestion = true;
            }
        }, 11000 );
    }
    
    sendMoves(): void
    {
        //this.zmqService.sendMoves();
        this.wsService.sendMoves();
        
        this.rollButtonClicked = false;
    }
    
    doMove( move: MoveDto ): void
    {
//         this.zmqService.doMove( move );
//         this.zmqService.sendMove( move );
        this.wsService.doMove( move );
        this.wsService.sendMove( move );
    }
    
    undoMove(): void
    {
//         this.zmqService.undoMove();
//         this.zmqService.sendUndo();
        this.wsService.undoMove();
        this.wsService.sendUndo();
    }
    
    myTurn(): boolean
    {
        return this.appStateService.myTurn();
    }
    
    doublingRequested(): boolean
    {
        return this.appStateService.doublingRequested();
    }
    
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    gameChanged( dto: GameDto ): void
    {
        this.setRollButtonVisible();
        this.setDicesVisible();
        this.setSendVisible();
        this.setUndoVisible();
        this.diceColor = dto?.currentPlayer;
        this.fireResize();
        this.newVisible = dto?.playState === GameState.ended;
        this.exitVisible = dto?.playState !== GameState.playing;
    }
    
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    diceChanged( dto: DiceDto[] ): void
    {
        this.dicesDto = dto;
        this.setRollButtonVisible();
        this.setSendVisible();
        this.setUndoVisible();
        this.fireResize();
        const game = this.appStateService.game.getValue();
        this.exitVisible = game?.playState !== GameState.playing && game?.playState !== GameState.requestedDoubling;
    }
    
    moveAnimFinished(): void
    {
//         this.zmqService.shiftMoveAnimationsQueue();
        this.wsService.shiftMoveAnimationsQueue();
    }
    
    @HostListener( 'window:resize', ['$event'] )
    onResize(): void
    {
        const _innerWidth   = $( '#GameBoardContainer' ).width() * 0.8;
        //alert( _innerWidth );
        
        this.width = Math.min( _innerWidth, 1024 );
        const span = this.messages?.nativeElement as Element;
        const spanWidth = span.getElementsByTagName( 'span' )[0].clientWidth;
        this.messageCenter = this.width / 2 - spanWidth / 2;
        
        this.height = Math.min( window.innerHeight - 40, this.width * 0.6 );
        
        const btnsOffset = 5; //Cheating. Could not get the height.
        const dices = this.dices?.nativeElement as HTMLElement;
        if ( dices ) {
            // Puts the dices on right side if its my turn.
            if ( this.myTurn() ) {
                dices.style.left = `${this.width / 2 + 20}px`;
                dices.style.right = '';
            } else {
                dices.style.right = `${this.width / 2 + 20}px`;
                dices.style.left = '';
            }
            dices.style.top = `${this.height / 2 - btnsOffset}px`;
        }
    }
    
    ngAfterViewInit(): void
    {
        this.fireResize();
    }
    
    fireResize(): void
    {
        setTimeout( () => {
            this.onResize();
        }, 1);
    }
    
    rollButtonClick(): void
    {
        this.rollButtonClicked = true;
        this.setRollButtonVisible();
        this.setDicesVisible();
        this.setSendVisible();
        this.fireResize();
        const gme = this.appStateService.game.getValue();
        if ( ! gme.validMoves || gme.validMoves.length === 0 ) {
            this.statusMessageService.setBlockedMessage();
        }
    }
    
    setRollButtonVisible(): void
    {
        if ( ! this.myTurn() ) {
            this.rollButtonVisible = false;
            return;
        }
        
        this.rollButtonVisible = !this.rollButtonClicked;
    }
    
    setSendVisible(): void
    {
        if ( ! this.myTurn() || !this.rollButtonClicked ) {
            this.sendVisible = false;
            return;
        }
        
        const game = this.appStateService.game.getValue();
        this.sendVisible = ! game || game.validMoves.length == 0;
    }
    
    setUndoVisible(): void
    {
        if ( ! this.myTurn() || this.doublingRequested() ) {
            this.undoVisible = false;
            return;
        }
        
        const dices = this.appStateService.dices.getValue();
        this.undoVisible = dices && dices.filter( ( d ) => d.used ).length > 0;
    }
    
    setDicesVisible(): void
    {
        if ( ! this.myTurn() ) {
            this.dicesVisible = true;
            return;
        }
        this.dicesVisible = !this.rollButtonVisible;
    }
    
    resignGame(): void
    {
//         this.zmqService.resignGame();
        this.wsService.resignGame();
    }
    
    newGame(): void
    {
        this.newVisible = false;
        this.started = false;
        this.rollButtonClicked = false;
        
//         this.zmqService.resetGame();
//         this.zmqService.connect( '', this.playAiFlag, this.forGodlFlag );
        this.wsService.resetGame();
        this.wsService.connect( '', this.playAiFlag, this.forGodlFlag );
        this.waitForOpponent();
    }
    
    exitGame(): void
    {
//         this.zmqService.exitGame();
        this.wsService.exitGame();
        this.appStateService.hideBusy();
        //this.router.navigateByUrl( '/lobby' );
    }
    
    selectGameRoom(): void
    {
        if ( ! this.isLoggedIn || ! this.hasPlayer ) {
            this.openRequirementsDialog();
            return;
        }
        
        if ( this.appState && this.appState.game ) {
            if ( ! this.appState.game.room ) {
                this.openSelectGameRoomDialog();
            }
        }
    }
    
    openSelectGameRoomDialog(): void
    {
        if ( this.appState && this.appState.game && this.appState.rooms ) {
            const modalRef = this.ngbModal.open( SelectGameRoomDialogComponent );
            
            modalRef.componentInstance.game     = this.appState.game;
            modalRef.componentInstance.rooms    = this.appState.rooms;
            modalRef.componentInstance.closeModal.subscribe( () => {
                modalRef.dismiss();
            });
        }
    }
    
    createGameRoom(): void
    {
        if ( ! this.isLoggedIn || ! this.hasPlayer ) {
            this.openRequirementsDialog();
            return;
        }
        
        if ( this.appState && this.appState.game ) {
            if ( ! this.appState.game.room ) {
                this.openCreateGameRoomDialog();
            }
        }
    }
    
    openCreateGameRoomDialog(): void
    {
        if ( this.appState && this.appState.game && this.appState.players ) {
            const modalRef = this.ngbModal.open( CreateGameRoomDialogComponent );
            
            modalRef.componentInstance.game     = this.appState.game;
            modalRef.componentInstance.players  = this.appState.players;
            modalRef.componentInstance.closeModal.subscribe( () => {
                modalRef.dismiss();
            });
        }
    }
    
    openRequirementsDialog(): void
    {
        const modalRef = this.ngbModal.open( RequirementsDialogComponent );
        
        modalRef.componentInstance.isLoggedIn   = this.isLoggedIn;
        modalRef.componentInstance.hasPlayer    = this.hasPlayer;
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
    }
}
