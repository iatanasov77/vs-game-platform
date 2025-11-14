import {
    Component,
    Inject,
    OnInit,
    AfterViewInit,
    OnDestroy,
    Input,
    Output,
    OnChanges,
    SimpleChanges,
    ViewChild,
    HostListener,
    EventEmitter,
    ElementRef
} from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { of, Observable, Subscription, map, merge, take } from 'rxjs';

import {
    selectGameRoom,
    selectGameRoomSuccess,
    startCardGame,
    startCardGameSuccess,
    loadGameBySlug,
    loadGameRooms
} from '../../../+store/game.actions';
import { GameState as MyGameState } from '../../../+store/game.reducers';

// App State
import { AppStateService } from '../../../state/app-state.service';
import { QueryParamsService } from '../../../state/query-params.service';
import { StatusMessage } from '../../../utils/status-message';

// Services
import { AuthService } from '../../../services/auth.service';
import { StatusMessageService } from '../../../services/status-message.service';
import { SoundService } from '../../../services/sound.service';
import { BridgeBeloteService } from '../../../services/websocket/bridge-belote.service';
import { GamePlayService } from '../../../services/game-play.service';

import GameCookieDto from '_@/GamePlatform/Model/Core/gameCookieDto';
import { CookieService } from 'ngx-cookie-service';
import { Keys } from '../../../utils/keys';

// CardGame Interfaces
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import BidType from '_@/GamePlatform/Model/CardGame/bidType';
import UserDto from '_@/GamePlatform/Model/Core/userDto';
import GameState from '_@/GamePlatform/Model/Core/gameState';
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';
import AnnounceDto from '_@/GamePlatform/Model/CardGame/announceDto';

// Dialogs
import { DebugGameSoundsComponent } from '../../game-dialogs/debug-game-sounds/debug-game-sounds.component';
import { RequirementsDialogComponent } from '../../game-dialogs/requirements-dialog/requirements-dialog.component';
import { SelectGameRoomDialogComponent } from '../../game-dialogs/select-game-room-dialog/select-game-room-dialog.component';
import { CreateGameRoomDialogComponent } from '../../game-dialogs/create-game-room-dialog/create-game-room-dialog.component';
import { CreateInviteGameDialogComponent } from '../../game-dialogs/create-invite-game-dialog/create-invite-game-dialog.component';
import { UserLoginDialogComponent } from '../../game-dialogs/user-login-dialog/user-login-dialog.component';

import { Helper } from '../../../utils/helper';

import templateString from './bridge-belote-container.component.html'
import styleString from './bridge-belote-container.component.scss'

declare var $: any;
declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Component({
    selector: 'bridge-belote-container',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class BridgeBeloteContainerComponent implements OnInit, AfterViewInit, OnDestroy, OnChanges
{
    @Input() lobbyButtonsVisible: boolean   = false;
    @Input() isLoggedIn: boolean        = false;
    @Input() hasPlayer: boolean         = false;
    
    @Output() lobbyButtonsVisibleChanged    = new EventEmitter<boolean>();
    @ViewChild( 'messages' ) messages: ElementRef | undefined;
    
    gameDto$: Observable<CardGameDto>;
    playerPosition$: Observable<PlayerPosition>;
    message$: Observable<StatusMessage>;
    timeLeft$: Observable<number>;
    
    user$: Observable<UserDto>;
    gameString$: Observable<string>;
    
    gameSubs: Subscription;
    playerCardsSubs: Subscription;
    playerBidsSubs: Subscription;
    playerAnnouncesSubs: Subscription;
    deckSubs: Subscription;
    pileSubs: Subscription;
    oponnetDoneSubs: Subscription;
    
    themeName: string;
    
    width: number = 710;
    height: number = 510;
    started = false;
    messageCenter = -15;
    gameId = "";
    playAiFlag = false;
    forGoldFlag = false;
    lokalStake = 0;
    playAiQuestion = false;
    
    gameDto: CardGameDto | undefined;
    newVisible = false;
    exitVisible = true;
    gameBiddingVisible = false;
    gameContractVisible = false;
    newRoundVisible = false;
    playerCardsDto: Array<CardDto[]> | undefined;
    playerBidsDto: BidDto[] | undefined = [];
    playerAnnouncesDto: Array<AnnounceDto[]> | undefined = [];
    deckDto: CardDto[] | undefined = [];
    pileDto: CardDto[] | undefined = [];
    validBids: BidDto[] = [];
    currentPlayer: PlayerPosition | undefined;
    contract: BidDto | undefined;
    
    appState?: MyGameState;
    gameStarted: boolean = false;
    
    isRoomSelected: boolean = false;
    hasRooms: boolean       = false;
    
    startedHandle: any;
    
    debugGameSoundsVisible = window.gamePlatformSettings.debugGameSounds;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( QueryParamsService ) private queryParamsService: QueryParamsService,
        @Inject( SoundService ) private sound: SoundService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( BridgeBeloteService ) private wsService: BridgeBeloteService,
        @Inject( CookieService ) private cookieService: CookieService,
        @Inject( GamePlayService ) private gamePlayService: GamePlayService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions,
        @Inject( NgbModal ) private ngbModal: NgbModal,
    ) {
        this.gameDto$ = this.appStateService.cardGame.observe();
        this.playerCardsSubs = this.appStateService.playerCards.observe().subscribe( this.playerCardsChanged.bind( this ) );
        this.playerBidsSubs = this.appStateService.playerBids.observe().subscribe( this.playerBidsChanged.bind( this ) );
        this.playerAnnouncesSubs = this.appStateService.playerAnnounces.observe().subscribe( this.playerAnnouncesChanged.bind( this ) );
        this.deckSubs = this.appStateService.deck.observe().subscribe( this.deckChanged.bind( this ) );
        this.pileSubs = this.appStateService.pile.observe().subscribe( this.pileChanged.bind( this ) );
        this.playerPosition$ = this.appStateService.myPosition.observe();
        
        this.gameSubs = this.appStateService.cardGame.observe().subscribe( this.gameChanged.bind( this ) );
        this.oponnetDoneSubs = this.appStateService.opponentDone.observe().subscribe( this.oponnentDone.bind( this ) );
        
        this.message$ = this.appStateService.statusMessage.observe();
        this.timeLeft$ = this.appStateService.moveTimer.observe();
        
        this.user$ = this.appStateService.user.observe();
        this.gameString$ = this.appStateService.gameString.observe();
        
        // if game page is refreshed, restore user from login cookie
        if ( ! this.appStateService.user.getValue() ) {
            this.authService.repair();
        }
        
        this.initFlags();
        
        if ( this.gameId.length ) {
            //this.wsService.connect( this.gameId, this.playAiFlag, this.forGoldFlag );
            this.playGame( this.gameId );
        }
        
        // For some reason i could not use an observable for theme. Maybe i'll figure out why someday
        // service.connect might need to be in a setTimeout callback.
        // this.themeName = this.appStateService.user.getValue()?.theme ?? 'card-game';
        this.themeName = 'card-game';
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
            // console.log( state.app.main );
            
            this.appState   = state.app.main;
            
            if ( state.app.main.gamePlay ) {
                this.gameStarted    = true;
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
    
        if ( ! this.lobbyButtonsVisible && ! this.playAiFlag ) {
            this.waitForOpponent();
        }
        this.fireResize();
    }
    
    ngOnDestroy(): void
    {
        this.gameSubs.unsubscribe();
        this.oponnetDoneSubs.unsubscribe();
        clearTimeout( this.startedHandle );
        this.appStateService.cardGame.clearValue();
        this.appStateService.myPosition.clearValue();
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
                case 'hasPlayer':
                    this.hasPlayer = changedProp.currentValue;
                    break;
            }
        }
    }
    
    openDebugGameSoundsDialog(): void
    {
        const modalRef = this.ngbModal.open( DebugGameSoundsComponent );
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
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
    
    doBid( bid: BidDto ): void
    {
        this.wsService.doBid( bid );
        this.wsService.sendBid( bid );
    }
    
    doPlayCard( card: CardDto ): void
    {
        if ( ! card.animate ) this.sound.playChecker();
        this.wsService.doPlayCard( card );
        this.wsService.sendPlayCard( card );
    }
    
    playCardAnimFinished(): void
    {
//         this.sound.playChecker();
//         this.wsService.shiftMoveAnimationsQueue();
    }
    
    login(): void
    {
        const modalRef = this.ngbModal.open( UserLoginDialogComponent );
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
    }
    
    resignGame(): void
    {
        this.wsService.resignGame();
    }
    
    newGame(): void
    {
        this.newVisible = false;
        this.started = false;
        
        this.wsService.resetGame();
        this.wsService.connect( '', false, false );
        this.waitForOpponent();
    }
    
    newRound(): void
    {
        this.gameContractVisible = false;
        this.gameBiddingVisible = true;
        this.wsService.startNewRound();
    }
    
    exitGame(): void
    {
        clearTimeout( this.startedHandle );
        this.wsService.exitGame();
        this.appStateService.hideBusy();
        
        this.gamePlayService.exitCardGame();
        this.playAiQuestion = false;
        this.lobbyButtonsVisibleChanged.emit( true );
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
    }
    
    cancelInvite(): void
    {
        this.exitGame();
    }
    
    selectGameRoom(): void
    {
        if ( ! this.isLoggedIn || ! this.hasPlayer ) {
            this.openRequirementsDialog();
            return;
        }
        
        if ( this.appState ) {
            if ( this.appState.game && ! this.appState.game.room ) {
                // Try With This Room Only For Now
                let gameRoom    = this?.appState?.rooms?.find( ( item: any ) => item?.slug === 'test-bridge-belote-room' );
                //console.log( 'Available Game Rooms', this?.appState?.rooms );
                //console.log( 'Selected Game Room', gameRoom );
                
                if ( gameRoom ) {
                    this.store.dispatch( selectGameRoom( { game: this.appState.game, room:  gameRoom } ) );
                }
            }
        }
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
    
    gameChanged( dto: CardGameDto ): void
    {
        if ( ! this.started && dto ) {
            if ( dto.playState === GameState.bidding ) {
                this.started = true;
                this.gameBiddingVisible = true;
                
                this.playAiQuestion = false;
                this.lobbyButtonsVisibleChanged.emit( false );
            }
        }
        
        if ( dto && dto.validBids && dto.validBids.length ) {
            this.validBids = dto.validBids;
        }
        
        if ( dto && dto.playState === GameState.playing ) {
            this.contract = dto.contract;
            this.playerBidsDto = [];
            this.gameBiddingVisible = false;
            this.gameContractVisible = true;
        }
        
        if ( dto && dto.playState === GameState.roundEnded ) {
            this.started = false;
            this.newRoundVisible = true;
        }
        
        // alert( 'Current Player: ' + dto?.currentPlayer + 'Play State: ' + dto?.playState );
        this.currentPlayer = dto?.currentPlayer;
    }
    
    oponnentDone(): void
    {
        
    }
    
    playerCardsChanged( dto: Array<CardDto[]> ): void
    {
        this.playerCardsDto = dto;
        this.fireResize();
    }
    
    playerBidsChanged( dto: BidDto[] ): void
    {
        this.playerBidsDto = dto;
        this.fireResize();
    }
    
    playerAnnouncesChanged( dto: Array<AnnounceDto[]> ): void
    {
        this.playerAnnouncesDto = dto;
        this.fireResize();
    }
    
    deckChanged( dto: CardDto[] ): void
    {
        this.deckDto = dto;
        this.fireResize();
    }
    
    pileChanged( dto: CardDto[] ): void
    {
        this.pileDto = dto;
        this.fireResize();
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
    }
    
    fireResize(): void
    {
        setTimeout( () => {
            this.onResize();
        }, 1);
    }
    
    playGame( gameId: string ): void
    {
        if ( ! gameId.length ) {
            this.gamePlayService.startCardGame();
        }
        
        this.initFlags();
        this.wsService.connect( gameId, this.playAiFlag, this.forGoldFlag );
        
        this.lobbyButtonsVisibleChanged.emit( false );
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
