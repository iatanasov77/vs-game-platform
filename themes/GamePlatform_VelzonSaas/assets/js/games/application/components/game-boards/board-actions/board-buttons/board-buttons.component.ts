import { Component, Inject, OnChanges, SimpleChanges, EventEmitter, Input, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import cssString from './board-buttons.component.scss';
import templateString from './board-buttons.component.html';

@Component({
    selector: 'app-board-buttons',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'CSS Not Loaded !!!',
    ]
})
export class BoardButtonsComponent implements OnChanges
{
    @Input() lobbyButtonsVisible        = false;
    @Input() isRoomSelected: boolean    = false;
    
    @Input() hasRooms: boolean          = false;
    @Input() newVisible = false;
    @Input() exitVisible = false;
    
    @Output() onSelectGameRoom = new EventEmitter<void>();
    @Output() onCreateGameRoom = new EventEmitter<void>();
    @Output() onNew = new EventEmitter<void>();
    @Output() onExit = new EventEmitter<void>();
    @Output() onResign = new EventEmitter<void>();
    @Output() onInviteFriend = new EventEmitter<void>();
    @Output() onPlayGame = new EventEmitter<void>();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService
    ) { }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'lobbyButtonsVisible':
                    this.lobbyButtonsVisible = changedProp.currentValue;
                    break;
                case 'isRoomSelected':
                    this.isRoomSelected = changedProp.currentValue;
                    break;
                case 'hasRooms':
                    this.hasRooms = changedProp.currentValue;
                    break;
                case 'newVisible':
                    this.newVisible = changedProp.currentValue;
                    break;
                case 'exitVisible':
                    this.exitVisible = changedProp.currentValue;
                    break;
            }
        }
    }
    
    newGame(): void
    {
        this.onNew.emit();
    }
    
    exitGame(): void
    {
        this.onExit.emit();
    }
    
    resign(): void
    {
        this.onResign.emit();
    }
    
    selectGameRoom(): void
    {
        this.onSelectGameRoom.emit();
    }
    
    createGameRoom(): void
    {
        this.onCreateGameRoom.emit();
    }
    
    playGame(): void
    {
        this.onPlayGame.emit();
    }
    
    inviteFriendClick(): void
    {
        this.onInviteFriend.emit();
    }
}
