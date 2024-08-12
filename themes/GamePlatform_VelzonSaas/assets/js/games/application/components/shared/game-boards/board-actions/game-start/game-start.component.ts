import { Component, Inject, Input, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';

import {
    startGame,
    startGameFailure,
    startGameSuccess,
    
    playerAnnounce,
    playerAnnounceFailure,
    playerAnnounceSuccess
} from '../../../../../+store/game.actions';
import { runStartGame, runMakeAnnounce } from '../../../../../+store/game.selectors';

import { UserNotLoggedInComponent } from '../../../dialogs/not-loggedin-dialog/not-loggedin-dialog.component';

import templateString from './game-start.component.html'
declare var $: any;

@Component({
    selector: 'game-start',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: []
})
export class GameStartComponent implements OnChanges
{
    @Input() isLoggedIn: boolean        = false;
    @Input() game: any;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
        
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) {
    
    }
    
    ngOnChanges( changes: SimpleChanges )
    {
        for ( const propName in changes ) {
            //alert( propName );
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'isLoggedIn':
                    this.isLoggedIn = changedProp.currentValue;
                    break;
                case 'game':
                    this.game = changedProp.currentValue;
                    break;
            }
        }
    }
    
    /*
    onStartGame( event: any ): void
    {
        //event.preventDefault();
        if ( ! this.isLoggedIn ) {
            this.openLoginForm();
            return;
        }
        
        this.game.startGame();
        $( '#btnStartGame' ).hide();
    }
    */
    
    onStartGame( event: any )
    {
        alert( this.isLoggedIn );
        
        this.store.dispatch( startGame() );
        this.store.subscribe( ( state: any ) => {
            //this.showSpinner    = state.main.latestTablatures == null;
        });
    }
    
    onPlayWithFriends( event: any )
    {
        if ( ! this.isLoggedIn ) {
            this.openLoginForm();
            return;
        }
    }
    
    openLoginForm(): void
    {
        const modalRef = this.ngbModal.open( UserNotLoggedInComponent );
        modalRef.componentInstance.closeModal.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
    }
}