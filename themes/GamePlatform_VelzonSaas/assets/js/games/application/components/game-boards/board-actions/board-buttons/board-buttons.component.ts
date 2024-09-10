import { Component, EventEmitter, Input, Output } from '@angular/core';

import cssString from './board-buttons.component.scss';
import templateString from './board-buttons.component.html';

@Component({
    selector: 'app-board-buttons',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'CSS Not Loaded !!!',
    ]
})
export class BoardButtonsComponent
{
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
