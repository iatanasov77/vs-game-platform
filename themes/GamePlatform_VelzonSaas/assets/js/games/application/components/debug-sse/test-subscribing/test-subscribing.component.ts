import { Component, OnInit, Inject } from '@angular/core';

import { User, PaymentTopic, MessageData } from '../../../services/debug-sse/models';
import { EventSourceServiceNew } from '../../../services/event-source.service-new'

import templateString from './test-subscribing.component.html';
import styleString from './test-subscribing.component.scss';

@Component({
    selector: 'app-test-subscribing',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestSubscribingComponent implements OnInit
{
    constructor(
        @Inject( EventSourceServiceNew ) private sseService: EventSourceServiceNew,
    ) { }
    
    ngOnInit(): void
    {
        const user: User = {
            id: '65PRG6RD0C87KAQV8RS8H5HHBR',
            name: 'Jose'
        };
        
        const topic = new PaymentTopic();
        this.sseService.createEventSource( user, topic ).subscribe (
            ( e: MessageData ) => {
                console.log( 'Message received: ' + e.message );
            }
        );
    }
}
