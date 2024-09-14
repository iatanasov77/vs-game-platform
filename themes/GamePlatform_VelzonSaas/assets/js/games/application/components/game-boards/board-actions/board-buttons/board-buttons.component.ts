import { Component, Inject, OnInit, OnChanges, SimpleChanges, EventEmitter, Input, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Store, State } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { Observable, map, merge, take } from 'rxjs';

import {
    selectGameRoom,
    selectGameRoomSuccess,
    startGame,
    startGameSuccess,
    loadGameRooms
} from '../../../../+store/game.actions';
import { GameState } from '../../../../+store/game.reducers';

import { RequirementsDialogComponent } from '../../../shared/requirements-dialog/requirements-dialog.component';
import { SelectGameRoomDialogComponent } from '../../select-game-room-dialog/select-game-room-dialog.component';

import cssString from './board-buttons.component.scss';
import templateString from './board-buttons.component.html';

@Component({
    selector: 'app-board-buttons',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'CSS Not Loaded !!!',
    ]
})
export class BoardButtonsComponent implements OnInit, OnChanges
{
    @Input() isLoggedIn: boolean        = false;
    @Input() hasPlayer: boolean         = false;
    @Input() isRoomSelected: boolean    = false;
    @Input() game: any;
    
    appState?: GameState;
    
    @Input() undoVisible = false;
    @Input() sendVisible = false;
    @Input() rollButtonVisible = false;
    @Input() newVisible = false;
    @Input() exitVisible = true;
    
    @Output() onUndoMove = new EventEmitter<void>();
    @Output() onSendMoves = new EventEmitter<void>();
    @Output() onRoll = new EventEmitter<void>();
    @Output() onNew = new EventEmitter<void>();
    @Output() onExit = new EventEmitter<void>();
    
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
        
        this.actions$.pipe( ofType( startGameSuccess ) ).subscribe( () => {
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
        
        if ( this.appState && this.appState.game ) {
            if ( ! this.appState.game.room ) {
                this.openSelectGameRoomDialog();
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
    
    undoMove(): void
    {
        this.onUndoMove.emit();
    }
    
    sendMoves(): void
    {
        this.onSendMoves.emit();
    }
    
    rollButtonClick(): void
    {
        this.onRoll.emit();
    }
    
    newGame(): void
    {
        this.onNew.emit();
    }
    
    exitGame(): void
    {
        this.onExit.emit();
    }
}
