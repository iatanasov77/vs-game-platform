import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import templateString from './backgammon-variants.component.html'
import cssString from './backgammon-variants.component.scss'

declare var $: any;

@Component({
    selector: 'app-backgammon-variants',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class BackgammonVariantsComponent implements OnInit, OnDestroy
{
    @Input() lobbyButtonsVisible: boolean   = false;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService
    ) {

    }
    
    ngOnInit(): void
    {

    }
    
    ngOnDestroy()
    {

    }
}
