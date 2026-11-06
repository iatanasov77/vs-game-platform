import { Component, OnInit, Inject, Input, NgZone } from '@angular/core';
import { Observable } from 'rxjs';

import { MessageData } from '@vankosoft/game-platform';

import { User, PaymentTopic } from '../../../services/debug-sse/models';
import { EventSourceService } from '../../../services/event-source.service'
import { FrontendPublisherService } from '../../../services/debug-sse/frontend-publisher.service'
import { BackendPublisherService } from '../../../services/debug-sse/backend-publisher.service'

import templateString from './test-user-payment.component.html';
import styleString from './test-user-payment.component.scss';

const { context } = require( '../../../context' );

@Component({
    standalone: false,
    selector: 'app-test-user-payment',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestUserPaymentComponent implements OnInit
{
    @Input() isLoggedIn: boolean = false;
    
    sseClient$: Observable<Event>;
    sseResponseType: string = 'event';
    
    constructor(
        @Inject( EventSourceService ) private sseService: EventSourceService,
        @Inject( FrontendPublisherService ) private frontendPublisher: FrontendPublisherService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
        @Inject( NgZone ) private zone: NgZone,
    ) {
        const user: User = {
            id: '65PRG6RD0C87KAQV8RS8H5HHBR',
            name: 'Jose'
        };
        const topic = new PaymentTopic( `https://topics.domain.com/${user.id}/payments` );
        
        this.sseClient$ = this.sseService.createNgxSseClientStream( topic.getTopic(), this.sseResponseType );
    }
    
    ngOnInit(): void
    {
        
    }
    
    testUserPayment(): void
    {
        /*  
        this.frontendPublisher.sendToTestUserPaymentTopic().subscribe( ( message ) => {
            alert( `Test User Payment Component Response: ${JSON.stringify( message )}` );
        });
        */
    }
}
