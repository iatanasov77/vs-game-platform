import {
    Component,
    Inject,
    OnInit,
    OnDestroy,
    Input,
    Output,
    OnChanges,
    SimpleChanges,
    EventEmitter
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

import IGame from '_@/GamePlatform/Model/GameInterface';
import * as GameEvents from '_@/GamePlatform/Game/GameEvents';

// App State
import { AppStateService } from '../../../state/app-state.service';
import { QueryParamsService } from '../../../state/query-params.service';
import { StatusMessage } from '../../../utils/status-message';

// Services
import { StatusMessageService } from '../../../services/status-message.service';
import { SoundService } from '../../../services/sound.service';
import { BridgeBeloteService } from '../../../services/websocket/bridge-belote.service';
import { GamePlayService } from '../../../services/game-play.service';

// CardGame Interfaces
import UserDto from '_@/GamePlatform/Model/Core/userDto';
import GameState from '_@/GamePlatform/Model/Core/gameState';
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

// Dialogs
import { RequirementsDialogComponent } from '../../game-dialogs/requirements-dialog/requirements-dialog.component';
import { SelectGameRoomDialogComponent } from '../../game-dialogs/select-game-room-dialog/select-game-room-dialog.component';
import { CreateGameRoomDialogComponent } from '../../game-dialogs/create-game-room-dialog/create-game-room-dialog.component';
import { CreateInviteGameDialogComponent } from '../../game-dialogs/create-invite-game-dialog/create-invite-game-dialog.component';
import { UserLoginDialogComponent } from '../../game-dialogs/user-login-dialog/user-login-dialog.component';

import { Helper } from '../../../utils/helper';

import templateString from './bridge-belote-container.component.html'
import styleString from './bridge-belote-container.component.scss'
declare var $: any;

@Component({
    selector: 'bridge-belote-container',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class BridgeBeloteContainerComponent implements OnInit, OnDestroy, OnChanges
{
    @Input() lobbyButtonsVisible: boolean   = false;
    @Input() isLoggedIn: boolean        = false;
    @Input() hasPlayer: boolean         = false;
    
    @Output() lobbyButtonsVisibleChanged    = new EventEmitter<boolean>();
    
    gameDto$: Observable<CardGameDto>;
    playerPosition$: Observable<PlayerPosition>;
    timeLeft$: Observable<number>;
    
    gameSubs: Subscription;
    oponnetDoneSubs: Subscription;
    
    themeName: string;
    
    width: number = 600;
    height: number = 400;
    started = false;
    gameId = "";
    playAiFlag = false;
    forGoldFlag = false;
    lokalStake = 0;
    
    newVisible = false;
    exitVisible = true;
    announceVisible = false;
    gameContractVisible = false;
    
    appState?: MyGameState;
    gameStarted: boolean = false;
    
    gameAnnounceIcon: any;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( QueryParamsService ) private queryParamsService: QueryParamsService,
        @Inject( SoundService ) private sound: SoundService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
        @Inject( BridgeBeloteService ) private wsService: BridgeBeloteService,
        @Inject( GamePlayService ) private gamePlayService: GamePlayService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions,
        @Inject( NgbModal ) private ngbModal: NgbModal,
    ) {
        this.gameAnnounceIcon   = null;
        
        const currentUrlparams = new URLSearchParams( window.location.search );
        let gameId = currentUrlparams.get( 'gameId' );
        if ( gameId ) {
            this.wsService.connect( '', false, false );
        }
        
        this.gameDto$ = this.appStateService.cardGame.observe();
        this.playerPosition$ = this.appStateService.myPosition.observe();
        
        this.gameSubs = this.appStateService.cardGame.observe().subscribe( this.gameChanged.bind( this ) );
        this.oponnetDoneSubs = this.appStateService.opponentDone.observe().subscribe( this.oponnentDone.bind( this ) );
        
        this.timeLeft$ = this.appStateService.moveTimer.observe();
        
        // For some reason i could not use an observable for theme. Maybe i'll figure out why someday
        // service.connect might need to be in a setTimeout callback.
        this.themeName = this.appStateService.user.getValue()?.theme ?? 'green';
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
        
        /*
        this.store.dispatch( loadGameBySlug( { slug: window.gamePlatformSettings.gameSlug } ) );
        
        this.actions$.pipe( ofType( startCardGameSuccess ) ).subscribe( () => {
            this.store.dispatch( loadGameRooms( { gameSlug: window.gamePlatformSettings.gameSlug } ) );
        });
        */
    }
    
    ngOnDestroy(): void
    {
        this.gameSubs.unsubscribe();
        this.oponnetDoneSubs.unsubscribe();
        //clearTimeout( this.startedHandle );
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
                case 'hasPlayer':
                    this.hasPlayer = changedProp.currentValue;
                    break;
            }
        }
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
        //this.waitForOpponent();
    }
    
    exitGame(): void
    {
        this.wsService.exitGame();
        this.appStateService.hideBusy();
        
        //this.gamePlayService.exitCardGame();
        //this.playAiQuestion = false;
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
    
    async playWithComputer()
    {
        /*
        this.wsService.exitGame();
        
        while ( this.appStateService.myConnection.getValue().connected ) {
            await Helper.delay( 500 );
        }
        */
        
        this.wsService.connect( '', true, false );
    }
    
    playWithFriends(): void
    {
        if ( this.appState && this.appState.game ) {
            this.store.dispatch( startCardGame( { game: this.appState.game } ) );
        }
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
            if ( dto.playState === GameState.bidding ) { // GameState.playing
                this.started = true;
                //this.playAiQuestion = false;
                this.lobbyButtonsVisibleChanged.emit( false );
            }
        }
        // console.log( dto?.id );
        // console.log( 'Debug GameDto: ', dto );
        // alert( this.lobbyButtonsVisible );
        
        this.setAnnounceVisible();
    }
    
    oponnentDone(): void
    {
        this.announceVisible = false;
    }
    
    setAnnounceVisible(): void
    {
        this.announceVisible = true;
    }
    
    playGame( gameId: string ): void
    {
        if ( ! gameId.length ) {
            //this.gamePlayService.startCardGame();
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
