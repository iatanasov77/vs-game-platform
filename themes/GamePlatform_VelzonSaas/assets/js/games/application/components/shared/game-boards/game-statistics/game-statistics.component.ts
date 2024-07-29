import { Component, OnInit, OnDestroy } from '@angular/core';

import templateString from './game-statistics.component.html'
import cssString from './game-statistics.component.scss'

declare var $: any;

@Component({
    selector: 'game-statistics',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GameStatisticsComponent implements OnInit, OnDestroy
{
    constructor() { }
    
    ngOnInit(): void
    {
        
    }
    
    ngOnDestroy()
    {

    }
}
