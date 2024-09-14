import { Component, Inject, Input, Output, EventEmitter } from '@angular/core';
import { NgForm } from '@angular/forms';
import { TranslateService } from '@ngx-translate/core';
import { Store } from '@ngrx/store';
import { selectGameRoom } from '../../../+store/game.actions';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';
import IPlayer from '_@/GamePlatform/Model/PlayerInterface';

import templateString from './create-game-room-dialog.component.html';

@Component({
    selector: 'create-game-room-dialog',
    template:  templateString || 'Template Not Loaded !!!',
    styleUrls: []
})
export class CreateGameRoomDialogComponent
{
    @Input() game: any;
    @Input() players: null | IPlayer[]  = null;
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( Store ) private store: Store,
    ) { }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    handleSubmit( form: NgForm ): void
    {
        let postData    = form.value;
        let player      = this?.players?.find( ( item: any ) => item?.id === postData.selectedRoom );
        
//         if ( this.game && gameRoom ) {
//             this.store.dispatch( selectGameRoom( { game: this.game, room:  gameRoom } ) );
//         }
        
        this.closeModal.emit();
    }
}