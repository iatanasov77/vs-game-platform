import { Component, Inject, Input, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import templateString from './backgammon-variants.component.html'
import cssString from './backgammon-variants.component.scss'

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Component({
    selector: 'app-backgammon-variants',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class BackgammonVariantsComponent implements OnChanges
{
    @Input() lobbyButtonsVisible: boolean   = false;
    game: string;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService
    ) {
        const currentUrlparams = new URLSearchParams( window.location.search );
        let variant = currentUrlparams.get( 'variant' );
        if ( variant == null ) {
            variant = 'normal';
        }
        
        this.game   = variant;
    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'lobbyButtonsVisible':
                    this.lobbyButtonsVisible = changedProp.currentValue;
                    break;
            }
        }
    }
}
