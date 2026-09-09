import { Component, Inject, Input, Output, EventEmitter } from '@angular/core';
import { NgForm } from '@angular/forms';
import { TranslateService } from '@ngx-translate/core';
import { IGameRoom } from '@vankosoft/game-platform';

import templateString from './select-game-room-dialog.component.html';

@Component({
    selector: 'select-game-room-dialog',
    template:  templateString || 'Template Not Loaded !!!',
    styleUrls: []
})
export class SelectGameRoomDialogComponent
{
    @Input() game: any;
    @Input() rooms: null | IGameRoom[]  = null;
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
    ) { }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    handleSubmit( form: NgForm ): void
    {
        let postData    = form.value;
        let gameRoom    = this?.rooms?.find( ( item: any ) => item?.id === postData.selectedRoom );
        
        this.closeModal.emit();
    }
}