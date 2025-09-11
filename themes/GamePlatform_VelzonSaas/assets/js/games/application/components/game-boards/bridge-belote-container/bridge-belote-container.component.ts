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

// Services
import { BridgeBeloteService } from '../../../services/websocket/bridge-belote.service';
import { GamePlayService } from '../../../services/game-play.service';

// BoardGame Interfaces
import UserDto from '_@/GamePlatform/Model/Core/userDto';
import GameState from '_@/GamePlatform/Model/Core/gameState';
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

// Dialogs
import { RequirementsDialogComponent } from '../../game-dialogs/requirements-dialog/requirements-dialog.component';

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
    
    width = 600;
    height = 400;
    started = false;
    
    announceVisible = false;
    gameContractVisible = false;
    
    appState?: MyGameState;
    gameAnnounceIcon: any;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
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
        this.themeName = this.appStateService.user.getValue()?.theme ?? 'dark';
    }
    
    ngOnInit(): void
    {
        this.store.subscribe( ( state: any ) => {
            this.appState   = state.app.main;
            
            if ( state.app.main.gamePlay ) {
                this.started    = true;
            }
            console.log( state.app.main );
        });
        
        this.store.dispatch( loadGameBySlug( { slug: window.gamePlatformSettings.gameSlug } ) );
        
        this.actions$.pipe( ofType( startCardGameSuccess ) ).subscribe( () => {
            this.store.dispatch( loadGameRooms( { gameSlug: window.gamePlatformSettings.gameSlug } ) );
        });
        
//         this.gameDto$.subscribe( game => {
//             if ( game ) {
//                 this.started = true;
//                 $( '#AnnounceContainer' ).show();
//             }
//         });
    }
    
    ngOnDestroy(): void
    {

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
            if ( dto.playState === GameState.playing ) {
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
}
