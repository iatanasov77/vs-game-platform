import { Component, Inject, Input, Output, EventEmitter, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import templateString from './game-start.component.html'

@Component({
    selector: 'game-start',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: []
})
export class GameStartComponent implements OnChanges
{
    @Input() isRoomSelected: boolean    = false;
    
    @Output() onSelectGameRoom      = new EventEmitter<void>();
    @Output() onPlayWithComputer    = new EventEmitter<void>();
    @Output() onPlayWithFriends     = new EventEmitter<void>();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
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
    
    clickSelectGameRoom( event: any ): void
    {
        this.onSelectGameRoom.emit();
    }
    
    clickPlayWithComputer( event: any ): void
    {
        this.onPlayWithComputer.emit();
    }
    
    clickPlayWithFriends( event: any ): void
    {
        this.onPlayWithFriends.emit();
    }
}
