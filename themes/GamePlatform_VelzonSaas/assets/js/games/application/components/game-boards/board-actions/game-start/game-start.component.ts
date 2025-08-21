import { Component, Inject, Input, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Store, State } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { Observable, map, merge, take } from 'rxjs';

import {
    selectGameRoom,
    selectGameRoomSuccess,
    startCardGame,
    startCardGameSuccess,
    loadGameBySlug,
    loadGameRooms
} from '../../../../+store/game.actions';
import { GameState } from '../../../../+store/game.reducers';

import { RequirementsDialogComponent } from '../../../game-dialogs/requirements-dialog/requirements-dialog.component';

declare var $: any;
declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

import templateString from './game-start.component.html'

@Component({
    selector: 'game-start',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: []
})
export class GameStartComponent implements OnInit, OnChanges
{
    @Input() isLoggedIn: boolean        = false;
    @Input() hasPlayer: boolean         = false;
    @Input() isRoomSelected: boolean    = false;
    @Input() game: any;
    
    appState?: GameState;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
        
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) { }
    
    ngOnInit(): void
    {
        this.store.subscribe( ( state: any ) => {
            console.log( state.app.main );
            this.appState   = state.app.main;
        });
        
        this.actions$.pipe( ofType( startCardGameSuccess ) ).subscribe( () => {
            this.store.dispatch( loadGameRooms() );
            this.game.startGame();
        });
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
                case 'isRoomSelected':
                    this.isRoomSelected = changedProp.currentValue;
                    break;
                case 'game':
                    this.game = changedProp.currentValue;
                    break;
            }
        }
    }
    
    onSelectGameRoom( event: any ): void
    {
        if ( ! this.isLoggedIn || ! this.hasPlayer ) {
            this.openRequirementsDialog();
            return;
        }
        
        if ( this.appState ) {
            if ( ! this.appState.game ) {
                this.store.dispatch( loadGameBySlug( { slug: window.gamePlatformSettings.gameSlug } ) );
            }
            
            if ( this.appState.game && ! this.appState.game.room ) {
                // Try With This Room Only For Now
                let gameRoom    = this?.appState?.rooms?.find( ( item: any ) => item?.slug === 'test-bridge-belote-room' );
                //console.log( gameRoom );
                
                if ( gameRoom ) {
                    this.store.dispatch( selectGameRoom( { game: this.appState.game, room:  gameRoom } ) );
                }
            }
        }
    }
    
    onPlayWithComputer( event: any ): void
    {
        if ( this.appState && this.appState.game ) {
            this.store.dispatch( startCardGame( this.appState ) );
        }
    }
    
    onPlayWithFriends( event: any ): void
    {
        if ( this.appState && this.appState.game ) {
            this.store.dispatch( startCardGame( this.appState ) );
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