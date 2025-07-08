import { Component, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

// App State
import { Busy } from '../../../state/busy';

import cssString from './busy.component.scss';
import templateString from './busy.component.html';

@Component({
    selector: 'app-busy',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class BusyComponent
{
    @Input() lobbyButtonsVisible: boolean = false;
    
    @Input() busy: Busy | null = null;
    @Input() overlay = true;
    text = '';
    
    constructor( @Inject( TranslateService ) private trans: TranslateService )
    {
        this.trans.onLangChange.subscribe( () => {
            this.text = this.trans.instant( 'pleasewait' );
        });
    }
}
