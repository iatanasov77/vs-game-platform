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

// Dialogs
import { RequirementsDialogComponent } from '../../game-dialogs/requirements-dialog/requirements-dialog.component';
import { SelectGameRoomDialogComponent } from '../../game-dialogs/select-game-room-dialog/select-game-room-dialog.component';
import { CreateGameRoomDialogComponent } from '../../game-dialogs/create-game-room-dialog/create-game-room-dialog.component';
import { PlayAiQuestionComponent } from '../../game-dialogs/play-ai-question/play-ai-question.component';
import { CreateInviteGameDialogComponent } from '../../game-dialogs/create-invite-game-dialog/create-invite-game-dialog.component';
import { UserLoginDialogComponent } from '../../game-dialogs/user-login-dialog/user-login-dialog.component';

// Services
import { AuthService } from '../../../services/auth.service';
import { BackgammonService } from '../../../services/websocket/backgammon.service';
import { StatusMessageService } from '../../../services/status-message.service';
import { SoundService } from '../../../services/sound.service';
import { EditorService } from '../../../services/editor.service';
import { TutorialService } from '../../../services/tutorial.service';
import { GamePlayService } from '../../../services/game-play.service';

import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';
import { CookieService } from 'ngx-cookie-service';
import { Keys } from '../../../utils/keys';

// App State
import { AppStateService } from '../../../state/app-state.service';
import { QueryParamsService } from '../../../state/query-params.service';
import { StatusMessage } from '../../../utils/status-message';
import { Busy } from '../../../state/busy';

// Board Interfaces
import UserDto from '_@/GamePlatform/Model/BoardGame/userDto';
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
export class BackgammonContainerComponent implements OnDestroy, AfterViewInit, OnChanges, OnInit
{
    @Input() lobbyButtonsVisible: boolean   = false;
    @Input() isLoggedIn: boolean            = false;
    @Input() introPlaying: boolean          = false;
    @Input() hasPlayer: boolean             = false;
    
    @Output() lobbyButtonsVisibleChanged    = new EventEmitter<boolean>();
    
    @ViewChild( 'dices' ) dices: ElementRef | undefined;
    @ViewChild( 'backgammonBoardButtons' ) backgammonBoardButtons: ElementRef | undefined;
    @ViewChild( 'messages' ) messages: ElementRef | undefined;
    
    gameDto$: Observable<GameDto>;
    dices$: Observable<DiceDto[]>;
    playerColor$: Observable<PlayerColor>;
    message$: Observable<StatusMessage>;
    timeLeft$: Observable<number>;
    
    user$: Observable<UserDto>;
    tutorialStep$: Observable<number>;
    gameString$: Observable<string>;
  
    gameSubs: Subscription;
    diceSubs: Subscription;
    rolledSubs: Subscription;
    oponnetDoneSubs: Subscription;
    
    themeName: string;
    
    width = 450;
    height = 450;
    started = false;
    rollButtonClicked = false;
    diceColor: PlayerColor | null = PlayerColor.neither;
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
    tutorial = false;
    editing = false;
    nextDoublingFactor = 1;
    introMuted = this.appStateService.user.getValue()?.muteIntro ?? false;
    
    rollButtonVisible = false;
    sendVisible = false;
    undoVisible = false;
    dicesVisible = false;
    newVisible = false;
    exitVisible = true;
    acceptDoublingVisible = false;
    requestDoublingVisible = false;
    requestHintVisible = false;
    dicesDto: DiceDto[] | undefined;
    gameDto: GameDto | undefined;
    
    appState?: MyGameState;
    gameStarted: boolean        = false;
    
    isRoomSelected: boolean = false;
    hasRooms: boolean       = false;
    
    gamePlayers: any;
    startedHandle: any;
    
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
        @Inject( EditorService ) private editService: EditorService,
        @Inject( TutorialService ) private tutorialService: TutorialService,
        @Inject( CookieService ) private cookieService: CookieService,
        @Inject( GamePlayService ) private gamePlayService: GamePlayService,
    ) {
        this.gameDto$ = this.appStateService.game.observe();
        this.dices$ = this.appStateService.dices.observe();
        this.diceSubs = this.appStateService.dices.observe().subscribe( this.diceChanged.bind( this ) );
        this.playerColor$ = this.appStateService.myColor.observe();
        this.playerColor$.subscribe( this.gotPlayerColor.bind( this ) );
        
        this.gameSubs = this.appStateService.game.observe().subscribe( this.gameChanged.bind( this ) );
        this.rolledSubs = this.appStateService.rolled.observe().subscribe( this.opponentRolled.bind( this ) );
        this.oponnetDoneSubs = this.appStateService.opponentDone.observe().subscribe( this.oponnentDone.bind( this ) );
      
        this.message$ = this.appStateService.statusMessage.observe();
        this.timeLeft$ = this.appStateService.moveTimer.observe();
        this.appStateService.moveTimer.observe().subscribe( this.timeTick.bind( this ) );
        
        this.user$ = this.appStateService.user.observe();
        this.tutorialStep$ = this.appStateService.tutorialStep.observe();
        this.gameString$ = this.appStateService.gameString.observe();
        
        this.user$.subscribe( ( user ) => {
            //alert( 'User: ' + user );
            if ( user ) this.introMuted = user.muteIntro;
        });
        
        // if game page is refreshed, restore user from login cookie
        if ( ! this.appStateService.user.getValue() ) {
            this.authService.repair();
        }
        
        this.initFlags();
        
        if ( this.tutorial ) {
            // Waiting for everything else before starting makes Input data update components.
            setTimeout( () => {
                this.tutorialService.start();
            }, 1 );
        } else if ( ! this.editing && this.gameId.length ) {
            //this.wsService.connect( this.gameId, this.playAiFlag, this.forGoldFlag );
            this.playGame( this.gameId );
        }
        
        if ( this.editing ) {
            this.exitVisible = true;
            this.newVisible = false;
            this.sendVisible = false;
            this.dicesVisible = false;
            this.editService.setStartPosition();
        }
        
        // For some reason i could not use an observable for theme. Maybe i'll figure out why someday
        // service.connect might need to be in a setTimeout callback.
        this.themeName = this.appStateService.user.getValue()?.theme ?? 'dark';
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
            //console.log( state.app.main );
            
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
    
        if ( ! this.lobbyButtonsVisible && ! this.playAiFlag && ! this.editing ) {
            this.waitForOpponent();
        }
        this.fireResize();
    }
    
    ngOnDestroy(): void
    {
        this.gameSubs.unsubscribe();
        this.diceSubs.unsubscribe();
        this.rolledSubs.unsubscribe();
        this.oponnetDoneSubs.unsubscribe();
        clearTimeout( this.startedHandle );
        this.appStateService.game.clearValue();
        this.appStateService.myColor.clearValue();
        this.appStateService.dices.clearValue();
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

    sendMoves(): void
    {
        this.wsService.sendMoves();
        this.rollButtonClicked = false;
        this.dicesVisible = false;
    }
    
    doMove( move: MoveDto ): void
    {
        if ( ! move.animate ) this.sound.playChecker();
        this.wsService.doMove( move );
        this.wsService.sendMove( move );
    }
    
    doEditMove( move: MoveDto ): void
    {
        this.editService.doMove( move );
        this.editService.updateGameString();
    }
    
    undoMove(): void
    {
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
    
    oponnentDone(): void
    {
        this.dicesVisible = false;
    }
    
    gameChanged( dto: GameDto ): void
    {
        if ( this.editing ) {
            this.fireResize();
            return;
        }
        
        if ( ! this.started && dto ) {
            if ( dto.playState === GameState.playing ) {
                clearTimeout( this.startedHandle );
                this.started = true;
                this.playAiQuestion = false;
                this.lobbyButtonsVisibleChanged.emit( false );
            }
            
            if ( dto.isGoldGame ) this.sound.playCoin();
        }
        // console.log( dto?.id );
        // console.log( 'Debug GameDto: ', dto );
        // alert( this.lobbyButtonsVisible );
        
        this.setRollButtonVisible();
        this.setSendVisible();
        this.setUndoVisible();
        this.setDoublingVisible( dto );
        this.diceColor = dto?.currentPlayer;
        this.fireResize();
        this.newVisible = dto?.playState === GameState.ended;
        this.exitVisible =
            dto?.playState !== GameState.playing &&
            dto?.playState !== GameState.requestedDoubling;
        this.nextDoublingFactor = dto?.goldMultiplier * 2;
        
        this.animateStake( dto );
    }
    
    animateStake( dto: GameDto )
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
    
    setDoublingVisible( gameDto: GameDto )
    {
        if ( ! gameDto ) return;
        
        this.acceptDoublingVisible =
            gameDto.isGoldGame &&
            gameDto.playState === GameState.requestedDoubling &&
            this.myTurn();
            
        // Visible if it is a gold-game and if it is my turn to double.
        const turn = this.appStateService.myColor.getValue() !== gameDto.lastDoubler;
        const rightType = gameDto.isGoldGame;
        
        this.requestDoublingVisible =
            turn &&
            rightType &&
            this.myTurn() &&
            this.rollButtonVisible &&
            gameDto.isGoldGame &&
            this.hasFundsForDoubling( gameDto );
    }
    
    hasFundsForDoubling( gameDto: GameDto ): boolean
    {
        return (
            gameDto.blackPlayer.gold >= gameDto.stake / 2 &&
            gameDto.whitePlayer.gold >= gameDto.stake / 2
        );
    }

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
        this.sound.playChecker();
        this.wsService.shiftMoveAnimationsQueue();
    }
    
    @HostListener( 'window:resize', ['$event'] )
    onResize(): void
    {
        //const _innerWidth   = window.innerWidth;
        const _innerWidth   = $( '#GameBoardContainer' ).width();
        //const _innerHeight   = window.innerHeight;
        const _innerHeight   = $( '#GameBoardContainer' ).height();
        
        //console.log( 'Window innerHeight', window.innerHeight );
        //console.log( 'Container innerHeight', $( '#GameBoardContainer' ).height() );
        
        this.width = Math.min( _innerWidth, 1024 );
        const span = this.messages?.nativeElement as Element;
        // console.log( span.getElementsByTagName( 'span' ) );
        const spanWidth = span.getElementsByTagName( 'span' )[0].clientWidth;
        // alert( spanWidth );
        
        this.messageCenter = this.width / 2 - spanWidth / 2;
        // alert( this.messageCenter );
        
        this.height = Math.min( _innerHeight - 40, this.width * 0.6 );
        
        const buttons = this.backgammonBoardButtons?.nativeElement as HTMLElement;
        const btnsOffset = 5; //Cheating. Could not get the height.
        if ( buttons ) {
            let buttonsTop  = this.height / 2 - btnsOffset;
            // My Workaround
            if ( $( 'canvas.game-board' ).hasClass( 'flipped' ) || $( 'canvas.game-board' ).hasClass( 'rotated' ) ) {
                buttonsTop  = buttonsTop - 10;
            }
            
            buttons.style.top = `${buttonsTop}px`;
            buttons.style.right = `${this.width * 0.11}px`;
        }
        
        const dices = this.dices?.nativeElement as HTMLElement;
        if ( dices ) {
            // Puts the dices on right side if its my turn.
            if ( this.myTurn() ) {
                dices.style.left = `${this.width / 2 + 20}px`;
                dices.style.right = '';
            } else {
                dices.style.right = `${this.width / 2 + 20}px`;
                dices.style.left = '';
                //alert( dices.style.right );
            }
            
            let dicesTop  = this.height / 2 - btnsOffset - 10;
            // My Workaround
            if ( $( 'canvas.game-board' ).hasClass( 'flipped' ) ) {
                dicesTop  = dicesTop - 15;
            }
            dices.style.top = `${dicesTop}px`;
        }
    }
    
    fireResize(): void
    {
        setTimeout( () => {
            this.onResize();
        }, 1);
    }
    
    rollButtonClick(): void
    {
        // alert( 'rollButtonClick()' );
        this.wsService.sendRolled();
        this.rollButtonClicked = true;
        this.setRollButtonVisible();
        this.dicesVisible = true;
    
        this.sound.playDice();
    
        this.setSendVisible();
        this.fireResize();
        this.requestDoublingVisible = false;
        
        const gme = this.appStateService.game.getValue();
        if( ! gme.validMoves || gme.validMoves.length === 0 ) {
            this.statusMessageService.setBlockedMessage();
        }
        this.changeDetector.detectChanges();
    }
    
    opponentRolled(): void
    {
        this.dicesVisible = true;
        this.sound.playDice();
        //alert( 'opponentRolled' );
    }
    
    setRollButtonVisible(): void
    {
        if ( ! this.myTurn() || this.doublingRequested() ) {
            this.rollButtonVisible = false;
            return;
        }
        
        this.rollButtonVisible = ! this.rollButtonClicked;
    }
    
    setSendVisible(): void
    {
        if ( ! this.myTurn() || ! this.rollButtonClicked || this.doublingRequested() ) {
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
    
    resignGame(): void
    {
        this.wsService.resignGame();
    }
    
    newGame(): void
    {
        this.newVisible = false;
        this.started = false;
        this.rollButtonClicked = false;
        
        this.wsService.resetGame();
        this.wsService.connect( '', this.playAiFlag, this.forGoldFlag );
        this.waitForOpponent();
    }
    
    exitGame(): void
    {
        clearTimeout( this.startedHandle );
        this.wsService.exitGame();
        this.appStateService.hideBusy();
        
        this.gamePlayService.exitBoardGame();
        this.playAiQuestion = false;
        this.lobbyButtonsVisibleChanged.emit( true );
    }
    
    requestDoubling(): void
    {
        this.requestDoublingVisible = false;
        this.wsService.requestDoubling();
    }
    
    requestHint(): void
    {
        this.requestHintVisible = false;
        this.wsService.requestHint();
    }
    
    acceptDoubling(): void
    {
        this.acceptDoublingVisible = false;
        this.wsService.acceptDoubling();
    }
    
    getDoubling( color: PlayerColor ): Observable<number>
    {
        return this.gameDto$.pipe(
            map( ( game ) => {
                return game?.lastDoubler === color ? game?.goldMultiplier : 0;
            })
        );
    }

    async playAi()
    {
        this.playAiQuestion = false;
        this.wsService.exitGame();
        
        while ( this.appStateService.myConnection.getValue().connected ) {
            await this.delay( 500 );
        }
        
        this.wsService.connect( '', true, this.forGoldFlag );
    }
    
    delay( ms: number )
    {
        return new Promise( ( resolve ) => setTimeout( resolve, ms ) );
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
        this.wsService.acceptInvite( inviteId );
        
        this.wsService.resetGame();
        this.wsService.connect( inviteId, this.playAiFlag, this.forGoldFlag );
        this.waitForOpponent();
    }
    
    cancelInvite(): void
    {
        this.exitGame();
    }
    
    login(): void
    {
        const modalRef = this.ngbModal.open( UserLoginDialogComponent );
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
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
    
    timeTick( time: number )
    {
        if ( time < 30 && this.myTurn() ) {
            const game = this.appStateService.game.getValue();
            if (
                game &&
                ! game.isGoldGame &&
                game.playState === GameState.playing &&
                ! this.rollButtonVisible &&
                ! this.undoVisible
            ) {
                this.requestHintVisible = true;
                return;
            }
        }
        this.requestHintVisible = false;
    }
    
    toggleMuted()
    {
        this.authService.toggleIntro();
    }
    
    playGame( gameId: string ): void
    {
        const game      = this.appStateService.game.getValue();
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
        this.tutorial = this.queryParamsService.tutorial.getValue() === true;
        this.editing = this.queryParamsService.editing.getValue() === true;
    }
}
