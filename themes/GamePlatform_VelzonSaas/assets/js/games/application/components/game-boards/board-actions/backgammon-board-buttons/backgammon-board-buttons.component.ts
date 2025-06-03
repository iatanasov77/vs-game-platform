import { Component, Inject, EventEmitter, Input, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import cssString from './backgammon-board-buttons.component.scss';
import templateString from './backgammon-board-buttons.component.html';

@Component({
    selector: 'backgammon-board-buttons',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'CSS Not Loaded !!!',
    ]
})
export class BackgammonBoardButtonsComponent
{
    @Input() undoVisible = false;
    @Input() sendVisible = false;
    @Input() rollButtonVisible = false;
    @Input() requestHintVisible = false;
    @Input() acceptDoublingVisible = false;
    @Input() requestDoublingVisible = false;
    
    @Output() onUndoMove = new EventEmitter<void>();
    @Output() onSendMoves = new EventEmitter<void>();
    @Output() onRoll = new EventEmitter<void>();
    @Output() onRequestHint = new EventEmitter<void>();
    @Output() onAcceptDoubling = new EventEmitter<void>();
    @Output() onRequestDoubling = new EventEmitter<void>();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService
    ) { }
    
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
    
    requestHint(): void
    {
        this.onRequestHint.emit();
    }
    
    acceptDoubling(): void
    {
        this.onAcceptDoubling.emit();
    }
    
    requestDoubling(): void
    {
        this.onRequestDoubling.emit();
    }
}
