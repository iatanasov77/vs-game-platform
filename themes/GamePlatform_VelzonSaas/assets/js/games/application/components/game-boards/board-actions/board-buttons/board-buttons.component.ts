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
    @Input() isRoomSelected: boolean    = false;
    @Input() undoVisible = false;
    @Input() sendVisible = false;
    @Input() rollButtonVisible = false;
    @Input() newVisible = false;
    @Input() exitVisible = true;
    
    @Output() onSelectGameRoom = new EventEmitter<void>();
    @Output() onUndoMove = new EventEmitter<void>();
    @Output() onSendMoves = new EventEmitter<void>();
    @Output() onRoll = new EventEmitter<void>();
    @Output() onNew = new EventEmitter<void>();
    @Output() onExit = new EventEmitter<void>();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService
    ) { }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'isRoomSelected':
                    this.isRoomSelected = changedProp.currentValue;
                    break;
            }
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
    
    selectGameRoom(): void
    {
        this.onSelectGameRoom.emit();
    }
}
