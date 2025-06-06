import { CommonModule } from '@angular/common';
import {Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import PlayerDto from '_@/GamePlatform/Model/BoardGame/playerDto';

import cssString from './board-player.component.scss';
import templateString from './board-player.component.html';

@Component({
    selector: 'app-board-player',
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'CSS Not Loaded !!!',
    ],
})
export class BoardPlayerComponent implements OnChanges
{
    @Input() playerDto?: PlayerDto;
    @Input() doubling: number | null = null;
    
    ainaUrl = '/assets/images/aina.png';
    
    constructor() {}
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        //console.log( changes );
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'playerDto':
                    this.playerDto = changedProp.currentValue;
                    break;
            }
        }
    }
    
    getPhotoUrl(): string
    {
        if ( ! this.playerDto?.photoUrl ) return '';
        
        return this.playerDto.photoUrl === 'aina'
                ? this.ainaUrl
                : this.playerDto.photoUrl;
    }
    
    getInitials(): string
    {
        if ( ! this.playerDto?.name ) return '';
        
        const names = this.playerDto.name.split( ' ' );
        let initials = '';
        
        for ( let i = 0; i < names.length; i++ ) {
            const name = names[i];
            if ( i < 2 ) initials += name.substr( 0, 1 );
        }
        
        return initials;
    }
}
