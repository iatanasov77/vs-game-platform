import { Component, OnInit, Inject, Input, NgZone } from '@angular/core';

import { MessageData } from '@vankosoft/game-platform';

import { User, PaymentTopic } from '../../../services/debug-sse/models';
import { EventSourceServiceTest } from '../../../services/event-source.service-test'
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
    
    constructor(
        @Inject( EventSourceServiceTest ) private sseService: EventSourceServiceTest,
        @Inject( FrontendPublisherService ) private frontendPublisher: FrontendPublisherService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
        @Inject( NgZone ) private zone: NgZone,
    ) { }
    
    ngOnInit(): void
    {
        this.subscribeToMercureTopic();
    }
    
    subscribeToMercureTopic(): void
    {
        const user: User = {
            id: '65PRG6RD0C87KAQV8RS8H5HHBR',
            name: 'Jose'
        };
        
        const topic = new PaymentTopic( `https://topics.domain.com/${user.id}/payments` );
        this.sseService.createNativeSseClientStream( topic ).subscribe (
            ( e: MessageData ) => {
                console.log( 'Message received: ' + e.message );
                alert( `Message received: ${e.message}` );
            }
        );
        
        // this.eventSourceService.createNgxSseClientStream( topic.getTopic() );
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
