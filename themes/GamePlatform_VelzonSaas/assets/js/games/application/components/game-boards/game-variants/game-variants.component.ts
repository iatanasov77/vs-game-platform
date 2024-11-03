import { Component, OnInit, OnDestroy, Inject } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import templateString from './game-variants.component.html'
import cssString from './game-variants.component.scss'

declare var $: any;

@Component({
    selector: 'app-game-variants',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GameVariantsComponent implements OnInit, OnDestroy
{
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
