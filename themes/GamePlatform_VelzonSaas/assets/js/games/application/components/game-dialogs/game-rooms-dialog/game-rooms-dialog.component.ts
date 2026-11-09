import { Component, Inject, Input, Output, EventEmitter } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IPlayer, GameRoomDto } from '@vankosoft/game-platform';

import templateString from './game-rooms-dialog.component.html';

@Component({
    standalone: false,
    selector: 'game-room-select-dialog',
    template:  templateString || 'Template Not Loaded !!!',
    styleUrls: []
})
export class GameRoomsDialogComponent
{
    @Input() game: any;
    @Input() rooms: GameRoomDto[] | null  = null;
    
    @Input() isLoggedIn: boolean = false;
    @Input() currentPlayer: IPlayer | undefined;
    
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
    ) { }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
}