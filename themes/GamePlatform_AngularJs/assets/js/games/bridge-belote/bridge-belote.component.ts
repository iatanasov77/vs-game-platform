import { Component, OnInit, OnDestroy } from '@angular/core';

import cssCardGameString from '../shared/CardGame.scss'
import cssGameString from './bridge-belote.component.scss'
import templateString from './bridge-belote.component.html'

declare var $: any;

@Component({
    selector: 'app-bridge-belote',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssCardGameString || 'Template Not Loaded !!!',
        cssGameString || 'Template Not Loaded !!!',
    ]
})
export class BridgeBeloteComponent implements OnInit, OnDestroy
{
    constructor() { }
    
    ngOnInit(): void
    {
        
    }
    
    ngOnDestroy()
    {

    }
}
