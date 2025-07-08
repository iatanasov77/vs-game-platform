import {
    Component,
    Input,
    OnChanges,
    SimpleChanges
} from '@angular/core';
import {
    trigger,
    state,
    style,
    animate,
    transition
} from '@angular/animations';
import { StatusMessage, MessageLevel } from '../../../utils/status-message';

import cssString from './messages.component.scss';
import templateString from './messages.component.html';

@Component({
    selector: 'app-messages',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ],
    
    animations: [
        trigger('showHide', [
            state(
                'initial',
                style({
                    left: '{{ initial }}px',
                    opacity: 0
                }),
                { params: { initial: 0 } }
            ),
            state(
                'shown',
                style({
                    left: '{{ shown }}px',
                    opacity: 1
                }),
                { params: { shown: 0 } }
            ),
            state(
                'hidden',
                style({
                    left: '0px',
                    opacity: 0
                })
            ),
            transition( 'shown => hidden', [animate('0.5s')] ),
            transition( 'hidden => initial', [animate('0.01s')] ),
            transition( 'initial => shown', [animate('1.0s')] )
        ])
    ]
})
export class MessagesComponent implements OnChanges
{
    @Input() message: StatusMessage | null = StatusMessage.getDefault();
    // changing the coordinates will affect all animations coordinates.
    @Input() initial = 0;
    @Input() shown = 0;
    
    state = 'hidden';
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'message':
                    this.animate();
                    break;
            }
        }
    }
    
    animate(): void
    {
        this.state = 'hidden';
        setTimeout( () => {
            this.state = 'initial';
            setTimeout( () => {
                this.state = 'shown';
            }, 100 );
        }, 500 );
    }
    
    getIcon(): string
    {
        if ( this.message?.level === MessageLevel.error ) {
            return 'fas fa-exclamation-circle red';
        }
        
        if ( this.message?.level === MessageLevel.warning ) {
            return 'fas fa-exclamation-triangle yellow';
        }
        
        return '';
    }
}
