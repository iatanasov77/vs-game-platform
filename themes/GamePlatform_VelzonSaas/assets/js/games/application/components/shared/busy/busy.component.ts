import { Component, Input, OnInit } from '@angular/core';

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
export class BusyComponent implements OnInit
{
    constructor() {}
    
    @Input() busy: Busy | null = null;
    @Input() text = 'Please wait.';
    @Input() overlay = true;
    
    ngOnInit(): void {}
    
    ngOnChanges(): void
    {
        // console.log(this.busy);
    }
}
