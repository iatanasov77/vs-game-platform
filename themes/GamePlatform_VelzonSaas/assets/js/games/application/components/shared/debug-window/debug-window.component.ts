import {
    Component,
    Inject,
    EventEmitter,
    Input,
    Output
} from '@angular/core';

import { TranslateService } from '@ngx-translate/core';

import cssString from './debug-window.component.scss';
import templateString from './debug-window.component.html';

@Component({
    standalone: false,
    selector: 'debug-window',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class DebugWindowComponent
{
    @Input() debugText: string = 'Test Debug Window';
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
    ) {
        
    }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
}