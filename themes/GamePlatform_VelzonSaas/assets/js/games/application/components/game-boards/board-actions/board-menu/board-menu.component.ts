import { Component, EventEmitter, Output } from '@angular/core';

import cssString from './board-menu.component.scss';
import templateString from './board-menu.component.html';

@Component({
    selector: 'app-board-menu',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'CSS Not Loaded !!!',
    ]
})
export class BoardMenuComponent
{
    @Output() flip = new EventEmitter<void>();
    @Output() resign = new EventEmitter<void>();
    open = false;
    
    openClick(): void
    {
        this.open = true;
    }
    
    closeClick(): void
    {
        this.open = false;
    }
    
    flipClick(): void
    {
        this.open = false;
        this.flip.emit();
    }
    
    resignClick(): void
    {
        this.open = false;
        this.resign.emit();
    }
}
