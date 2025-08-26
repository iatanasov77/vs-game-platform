import {
    Component,
    OnInit,
    OnDestroy,
    AfterViewInit,
    OnChanges,
    SimpleChanges,
    Inject,
    ElementRef,
    ChangeDetectorRef,
    Input,
    Output,
    ViewChild,
    HostListener,
    EventEmitter
} from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable, Subscription, map } from 'rxjs';
import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import {
    selectGameRoom,
    selectGameRoomSuccess,
    loadGameRooms
} from '../../../+store/game.actions';
import { GameState as MyGameState } from '../../../+store/game.reducers';

// NgxChessBoard API Reference: https://www.npmjs.com/package/ngx-chess-board
import { NgxChessBoardView, NgxChessBoardService } from 'ngx-chess-board';

import GameCookieDto from '_@/GamePlatform/Model/Core/gameCookieDto';
import { CookieService } from 'ngx-cookie-service';
import { Keys } from '../../../utils/keys';

// Dialogs
import { RequirementsDialogComponent } from '../../game-dialogs/requirements-dialog/requirements-dialog.component';
import { CreateInviteGameDialogComponent } from '../../game-dialogs/create-invite-game-dialog/create-invite-game-dialog.component';

// App State
import { AppStateService } from '../../../state/app-state.service';
import { QueryParamsService } from '../../../state/query-params.service';
import { StatusMessage } from '../../../utils/status-message';

// Services
import { AuthService } from '../../../services/auth.service';
import { BackgammonService } from '../../../services/websocket/backgammon.service';
import { StatusMessageService } from '../../../services/status-message.service';
import { SoundService } from '../../../services/sound.service';
import { GamePlayService } from '../../../services/game-play.service';

// BoardGame Interfaces
import UserDto from '_@/GamePlatform/Model/Core/userDto';
import GameState from '_@/GamePlatform/Model/Core/gameState';
import BoardGameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';

import { Helper } from '../../../utils/helper';
import { IThemes, DarkTheme } from './themes';

import cssGameString from './chess-container.component.scss';
import templateString from './chess-container.component.html';

declare var $: any;

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Component({
    selector: 'app-chess-container',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class ChessContainerComponent implements OnInit, AfterViewInit, OnDestroy, OnChanges
{
    @Input() lobbyButtonsVisible: boolean   = false;
    @Input() isLoggedIn: boolean            = false;
    @Input() introPlaying: boolean          = false;
    @Input() hasPlayer: boolean             = false;
    
    @Output() lobbyButtonsVisibleChanged    = new EventEmitter<boolean>();
    
    @ViewChild( 'messages' ) messages: ElementRef | undefined;
    @ViewChild( 'board', {static: false} ) board: NgxChessBoardView | undefined;
    
    gameDto$: Observable<BoardGameDto>;
    playerColor$: Observable<PlayerColor>;
    message$: Observable<StatusMessage>;
    timeLeft$: Observable<number>;
    
    user$: Observable<UserDto>;
    gameString$: Observable<string>;
    
    gameSubs: Subscription;
    
    width = 450;
    height = 450;
    started = false;
    messageCenter = 0;
    rotated = false;
    flipped = false;
    gameId = "";
    playAiFlag = false;
    forGoldFlag = false;
    PlayerColor = PlayerColor;
    lokalStake = 0;
    animatingStake = false;
    playAiQuestion = false;
    introMuted = this.appStateService.user.getValue()?.muteIntro ?? false;
    
    gameDto: BoardGameDto | undefined;
    newVisible = false;
    exitVisible = true;
    undoVisible = false;
    
    appState?: MyGameState;
    gameStarted: boolean        = false;
    
    isRoomSelected: boolean = false;
    hasRooms: boolean       = false;
    
    startedHandle: any;
    
    theme: IThemes = new DarkTheme();
    
    constructor(
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions,
        @Inject( NgbModal ) private ngbModal: NgbModal,
        @Inject( ChangeDetectorRef ) private changeDetector: ChangeDetectorRef,
        
        @Inject( AuthService ) private authService: AuthService,
        @Inject( BackgammonService ) private wsService: BackgammonService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( QueryParamsService ) private queryParamsService: QueryParamsService,
        @Inject( SoundService ) private sound: SoundService,
        @Inject( CookieService ) private cookieService: CookieService,
        @Inject( GamePlayService ) private gamePlayService: GamePlayService,
        
        @Inject( NgxChessBoardService ) private ngxChessBoardService: NgxChessBoardService,
    ) {
        this.gameDto$ = this.appStateService.boardGame.observe();
        this.playerColor$ = this.appStateService.myColor.observe();
        this.playerColor$.subscribe( this.gotPlayerColor.bind( this ) );
        
        this.gameSubs = this.appStateService.boardGame.observe().subscribe( this.gameChanged.bind( this ) );
        
        this.message$ = this.appStateService.statusMessage.observe();
        this.timeLeft$ = this.appStateService.moveTimer.observe();
        
        this.user$ = this.appStateService.user.observe();
        this.gameString$ = this.appStateService.gameString.observe();
    }
    
    ngOnInit(): void
    {
        this.authService.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            this.isLoggedIn = isLoggedIn;
            let auth        = this.authService.getAuth();
            
            if ( isLoggedIn && auth ) {
                this.statusMessageService.setNotGameStarted();
            }
        });
        
        this.gameDto$.subscribe( res => {
            this.gameDto = res;
        });
        
        this.store.subscribe( ( state: any ) => {
            console.log( state.app.main );
            
            this.appState   = state.app.main;
            this.hasRooms   = this?.appState?.rooms?.length && this?.appState?.rooms?.length > 0 ? true : false;
            
            if ( state.app.main.gamePlay ) {
                this.gameStarted    = true;
                this.statusMessageService.setWaitingForConnect();
            }
            
            this.fireResize();
        });
        
        /**
         * Cannot Remove Game Rooms from Board Games Because Game Room is a Game Session for Now.
         */
        this.actions$.pipe( ofType( selectGameRoomSuccess ) ).subscribe( () => {
            this.newVisible = false;
            this.exitVisible = false;
            
            let gameCookie  = this.cookieService.get( Keys.gameIdKey );
            //alert( gameCookie );
            if ( gameCookie ) {
                let gameCookieDto   = JSON.parse( gameCookie ) as GameCookieDto;
                
                gameCookieDto.roomSelected = true;
                this.cookieService.set( Keys.gameIdKey, JSON.stringify( gameCookieDto ), 2 );
            }
            
            this.isRoomSelected = true;
        });
    }
    
    ngAfterViewInit(): void
    {
        this.playAiQuestion = false;
        this.lokalStake = 0;
    
        if ( ! this.lobbyButtonsVisible && ! this.playAiFlag ) {
            this.waitForOpponent();
        }
        this.fireResize();
    }
    
    ngOnDestroy(): void
    {
        this.gameSubs.unsubscribe();
        clearTimeout( this.startedHandle );
        this.appStateService.boardGame.clearValue();
        this.appStateService.myColor.clearValue();
        this.appStateService.messages.clearValue();
        this.appStateService.moveTimer.clearValue();
        this.started = false;
        this.wsService.exitGame();
        this.sound.fadeIntro();
    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'isLoggedIn':
                    this.isLoggedIn = changedProp.currentValue;
                    break;
                case 'lobbyButtonsVisible':
                    this.lobbyButtonsVisible = changedProp.currentValue;
                    break;
                case 'introPlaying':
                    this.introPlaying = changedProp.currentValue;
                    break;
                case 'hasPlayer':
                    this.hasPlayer = changedProp.currentValue;
                    break;
            }
        }
    }
    
    @HostListener( 'window:resize', ['$event'] )
    onResize(): void
    {
        const _innerWidth   = $( '#GameBoardContainer' ).width();
        
        this.width = Math.min( _innerWidth, 1024 );
        const span = this.messages?.nativeElement as Element;
        // console.log( span.getElementsByTagName( 'span' ) );
        const spanWidth = span.getElementsByTagName( 'span' )[0].clientWidth;
        // alert( spanWidth );
        
        this.messageCenter = this.width / 2 - spanWidth / 2;
    }
    
    fireResize(): void
    {
        setTimeout( () => {
            this.onResize();
        }, 1);
    }
    
    gameChanged( dto: BoardGameDto ): void
    {
        if ( ! this.started && dto ) {
            if ( dto.playState === GameState.playing ) {
                clearTimeout( this.startedHandle );
                this.started = true;
                this.playAiQuestion = false;
                this.lobbyButtonsVisibleChanged.emit( false );
            }
            
            if ( dto.isGoldGame ) this.sound.playCoin();
        }
        
        this.setUndoVisible();
        
        this.fireResize();
        this.newVisible = dto?.playState === GameState.ended;
        this.exitVisible =
            dto?.playState !== GameState.playing &&
            dto?.playState !== GameState.requestedDoubling;
        
        this.animateStake( dto );
    }
    
    animateStake( dto: BoardGameDto )
    {
        if ( dto && dto.isGoldGame && dto.stake !== this.lokalStake ) {
            this.animatingStake = true;
            const step = Math.ceil( ( dto.stake - this.lokalStake ) / 10 );
            setTimeout( () => {
                const handle = setInterval( () => {
                    this.lokalStake += step;
                    this.changeDetector.detectChanges();
                    
                    if (
                        ( step > 0 && this.lokalStake >= dto.stake ) ||
                        ( step < 0 && this.lokalStake <= dto.stake )
                    ) {
                        clearInterval( handle );
                        this.lokalStake = dto.stake;
                        this.animatingStake = false;
                    }
                }, 100 );
            }, 100 ); // Give time to show everything
        }
    }
    
    private waitForOpponent()
    {
        this.sound.playPianoIntro();
        this.startedHandle = setTimeout( () => {
            if ( ! this.started && ! this.lobbyButtonsVisible ) {
                //alert( this.appStateService.user );
                if ( this.appStateService.user?.getValue() ) {
                    this.playAiQuestion = true;
                } else {
                    this.appStateService.hideBusy();
                }
            }
        }, 11000 );
    }
    
    gotPlayerColor()
    {
        if ( this.appStateService.myColor.getValue() == PlayerColor.white ) {
            this.flipped = true;
        }
    }
    
    myTurn(): boolean
    {
        return this.appStateService.myTurn();
    }
    
    setUndoVisible(): void
    {
        if ( ! this.myTurn() ) {
            this.undoVisible = false;
            return;
        }
        
        //this.undoVisible = dices && dices.filter( ( d ) => d.used ).length > 0;
    }
    
    resignGame(): void
    {
        this.wsService.resignGame();
    }
    
    newGame(): void
    {
        this.newVisible = false;
        this.started = false;
        
        this.board?.reset();
        
        this.wsService.resetGame();
        this.wsService.connect( '', this.playAiFlag, this.forGoldFlag );
        this.waitForOpponent();
    }
    
    exitGame(): void
    {
        this.board?.reset();
        
        clearTimeout( this.startedHandle );
        this.wsService.exitGame();
        this.appStateService.hideBusy();
        
        this.gamePlayService.exitBoardGame();
        this.playAiQuestion = false;
        this.lobbyButtonsVisibleChanged.emit( true );
    }
    
    async playAi()
    {
        this.playAiQuestion = false;
        this.wsService.exitGame();
        
        while ( this.appStateService.myConnection.getValue().connected ) {
            await Helper.delay( 500 );
        }
        
        this.wsService.connect( '', true, this.forGoldFlag );
    }
    
    keepWaiting(): void
    {
        this.sound.playBlues();
        this.playAiQuestion = false;
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
    
    inviteFriend(): void
    {
        const modalRef = this.ngbModal.open( CreateInviteGameDialogComponent );
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
        
        modalRef.componentInstance.onPlayGame.subscribe( ( gameId: string ) => {
            modalRef.close();
            
            this.playGame( gameId );
        });
    }
    
    acceptInvite( inviteId: string ): void
    {
        this.board?.reset();
        
        this.wsService.acceptInvite( inviteId );
        
        this.wsService.resetGame();
        this.wsService.connect( inviteId, this.playAiFlag, this.forGoldFlag );
        this.waitForOpponent();
    }
    
    cancelInvite(): void
    {
        this.exitGame();
    }
    
    /*
     * The coords parameter contains source and destination position e.g. 'd2d4'.
     */
    onMakeMove( coords: string ): void
    {
        console.log( 'Move Coordinates', coords );
    }
    
    onFlipped(): void
    {
        this.flipped = ! this.flipped;
        // both flipped and rotated is not supported
        if ( this.flipped ) {
            this.rotated = false;
        }
    }
    
    onRotated(): void
    {
        this.rotated = ! this.rotated;
        if ( this.rotated ) {
            this.flipped = false;
        }
    }
    
    toggleMuted()
    {
        this.authService.toggleIntro();
    }
    
    playGame( gameId: string ): void
    {
        const game      = this.appStateService.boardGame.getValue();
        const myColor   = this.appStateService.myColor.getValue();
        //console.log( 'GameDto Object: ', game );
        
        if ( ! gameId.length ) {
            this.gamePlayService.startBoardGame( 'normal' );
        }
        
        this.initFlags();
        this.wsService.connect( gameId, this.playAiFlag, this.forGoldFlag );
        
        this.lobbyButtonsVisibleChanged.emit( false );
        this.waitForOpponent();
        window.dispatchEvent( new Event( 'resize' ) );
        
        this.statusMessageService.setWaitingForConnect();
        this.exitVisible = true;
    }
    
    initFlags(): void
    {
        if ( this.queryParamsService.gameId.getValue() ) {
            this.gameId = this.queryParamsService.gameId.getValue();
        }
        
        this.playAiFlag = this.queryParamsService.playAi.getValue() === true;
        this.forGoldFlag = this.queryParamsService.forGold.getValue() === true;
        this.lokalStake = 0;
    }
}
