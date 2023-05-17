import { Component, OnInit, OnDestroy } from '@angular/core';

import templateString from './game-statistics.component.html'

declare var $: any;

@Component({
    selector: 'game-statistics',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: []
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
