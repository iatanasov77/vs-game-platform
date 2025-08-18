import { Component, Inject, Input, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { GameVariant } from "../../../../game.variant";

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
    
    readonly BACKGAMMON_NORMAL  = GameVariant.BACKGAMMON_NORMAL;
    readonly BACKGAMMON_TAPA    = GameVariant.BACKGAMMON_TAPA;
    readonly BACKGAMMON_GULBARA = GameVariant.BACKGAMMON_GULBARA;
    
    game: string;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService
    ) {
        const currentUrlparams = new URLSearchParams( window.location.search );
        let variant = currentUrlparams.get( 'variant' );
        if ( variant == null ) {
            variant = GameVariant.BACKGAMMON_NORMAL;
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
