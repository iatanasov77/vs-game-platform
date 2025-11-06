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

declare var $: any;

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
                    left: '{{ shown }}px',
                    transform: 'scale(3)',
                    opacity: 0.3,
                    top: 40
                }),
                { params: { shown: 0 } }
            ),
            state(
                'shown',
                style({
                    left: '{{ shown }}px',
                    transform: 'scale(1)',
                    opacity: 1,
                    top: 0
                }),
                { params: { shown: 0 } }
            ),
            state(
                'card-table-shown',
                style({
                    left: '{{ shown }}px',
                    transform: 'scale(1)',
                    opacity: 1,
                    top: '-15px',
                    color: 'black'
                }),
                { params: { shown: 0 } }
            ),
            state(
                'shown-flipped',
                style({
                    left: '{{ shown }}px',
                    transform: 'scale(1)',
                    opacity: 1,
                    top: '-10px'
                }),
                { params: { shown: 0 } }
            ),
            state(
                'hidden',
                style({
                    left: '0px',
                    opacity: 0,
                    transform: 'scale(1)'
                })
            ),
            transition( 'shown => hidden', [animate('0.5s ease-out')] ),
            transition( 'hidden => initial', [animate('0.01s')] ),
            
            // My Workaround
            transition( 'shown-flipped => hidden', [animate('0.5s ease-out')] ),
            transition( 'initial => shown-flipped', [animate('1s ease')] ),
        ])
    ]
})
export class MessagesComponent implements OnChanges
{
    @Input() message: StatusMessage | null = StatusMessage.getDefault();
    // changing the coordinates will affect all animations coordinates.
    
    @Input() initial = 0;
    @Input() shown = 0; //x coordinate when shown.
    
    state = 'hidden';
    animating = false;
    
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
        if ( this.animating ) return;
        this.animating = true;
    
        this.state = 'hidden';
        setTimeout( () => {
            this.state = 'initial';
            setTimeout( () => {
                // My Workaround
                if ( $( 'canvas.card-table' ).hasClass( 'flipped' ) || $( 'canvas.card-table' ).hasClass( 'rotated' ) ) {
                    this.state = 'shown-flipped';
                } else {
                    this.state = 'card-table-shown';
                }
                this.animating = false;
            }, 100 );
        }, 500 );
    }
    
    getIcon(): string
    {
        if ( this.message?.level === MessageLevel.error ) {
            return 'fas fa-exclamation-circle error-color';
        }
        
        if ( this.message?.level === MessageLevel.warning ) {
            return 'fas fa-exclamation-triangle yellow';
        }
        
        return '';
    }
}
