import { Component, Inject, Input, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import { AppStateService } from '../../../state/app-state.service';

import cssString from './button.component.scss';
import templateString from './button.component.html';

@Component({
    selector: 'app-button',
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class ButtonComponent implements OnInit
{
    @Input() default = false;
    @Input() type = 'button';
    @Input() small = false;
    
    theme: Observable<string>;
    
    constructor( @Inject( AppStateService ) private appState: AppStateService )
    {
        this.theme = this.appState.theme.observe();
    }
    
    ngOnInit(): void
    {
        setTimeout(() => {
            this.appState.theme.next();
        }, 1);
    }
}
