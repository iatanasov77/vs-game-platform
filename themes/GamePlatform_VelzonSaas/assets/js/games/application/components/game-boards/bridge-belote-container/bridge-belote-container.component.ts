import { Component, Inject, OnInit, OnDestroy, Input, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { of, Observable, map, merge, take } from 'rxjs';

import {
    selectGameRoom,
    selectGameRoomSuccess,
    startCardGame,
    startCardGameSuccess,
    loadGameBySlug,
    loadGameRooms
} from '../../../+store/game.actions';
import { GameState } from '../../../+store/game.reducers';

import IGame from '_@/GamePlatform/Model/GameInterface';
import * as GameEvents from '_@/GamePlatform/Game/GameEvents';

// App State
import { AppStateService } from '../../../state/app-state.service';

// Services
import { BridgeBeloteService } from '../../../services/websocket/bridge-belote.service';
import { GamePlayService } from '../../../services/game-play.service';

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
    @Input() isLoggedIn: boolean        = false;
    @Input() hasPlayer: boolean         = false;
    @Input() developementClass: string  = '';
    @Input() gameProvider?: any;
    @Input() game?: any;
    
    appState?: GameState;
    gameStarted: boolean                = false;
    gameAnnounceIcon: any;
    announceSymbols: any;
    
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
        
        this.wsService.connect( '', false, false );
    }
    
    ngOnInit(): void
    {
        this.announceSymbols    = this.gameProvider?.getAnnounceSymbols();
        
        this.store.subscribe( ( state: any ) => {
            this.appState   = state.app.main;
            
            if ( state.app.main.gamePlay ) {
                this.gameStarted    = true;
            }
            console.log( state.app.main );
        });
        
        this.store.dispatch( loadGameBySlug( { slug: window.gamePlatformSettings.gameSlug } ) );
        
        this.actions$.pipe( ofType( startCardGameSuccess ) ).subscribe( () => {
            this.store.dispatch( loadGameRooms( { gameSlug: window.gamePlatformSettings.gameSlug } ) );
            this.game.startGame();
        });
    }
    
    ngOnDestroy(): void
    {

    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'developementClass':
                    this.developementClass = changedProp.currentValue;
                    break;
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
}
