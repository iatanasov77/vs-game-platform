import { Component, Inject, Input } from '@angular/core';
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
export class BackgammonVariantsComponent
{
    @Input() lobbyButtonsVisible: boolean   = false;
    game: string;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService
    ) {
        this.game   = window.gamePlatformSettings.gameSlug;
    }
}
