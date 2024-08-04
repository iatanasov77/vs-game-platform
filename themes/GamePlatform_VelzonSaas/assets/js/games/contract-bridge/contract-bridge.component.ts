import { Component, OnInit, OnDestroy } from '@angular/core';

import cssGameString from './contract-bridge.component.scss'
import templateString from './contract-bridge.component.html'

declare var $: any;

@Component({
    selector: 'app-contract-bridge',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Template Not Loaded !!!',
    ]
})
export class ContractBridgeComponent implements OnInit, OnDestroy
{
    constructor() { }
    
    ngOnInit(): void
    {
        
    }
    
    ngOnDestroy()
    {

    }
}
