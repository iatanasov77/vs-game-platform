import { Component, OnInit, Inject, Input, Output, EventEmitter, NgZone } from '@angular/core';

import { MessageData } from '@vankosoft/game-platform';

import { AuthService } from '../../../services/auth.service';
import { EventSourceServiceTest } from '../../../services/event-source.service-test'

import { MercureJwt, User, PaymentTopic } from '../../../services/debug-sse/models';
import { FrontendPublisherService } from '../../../services/debug-sse/frontend-publisher.service'
import { BackendPublisherService } from '../../../services/debug-sse/backend-publisher.service'

import templateString from './test-subscribing.component.html';
import styleString from './test-subscribing.component.scss';

const { context } = require( '../../../context' );
declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Component({
    standalone: false,
    selector: 'app-test-subscribing',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestSubscribingComponent implements OnInit
{
    @Input() isLoggedIn: boolean = false;
    @Output() onOpenDebugWindow = new EventEmitter<string>();
    
    topicUri: string;
    
    constructor(
        @Inject( AuthService ) private authService: AuthService,
        @Inject( EventSourceServiceTest ) private sseService: EventSourceServiceTest,
        @Inject( FrontendPublisherService ) private frontendPublisher: FrontendPublisherService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
        @Inject( NgZone ) private zone: NgZone,
    ) { 
        this.topicUri = `${context.backendURL}/test-subscribing/subscribe`;
    }
    
    ngOnInit(): void
    {
        this.subscribeToMercureTopic();
    }
    
    subscribeToMercureTopic(): void
    {
        this.sseService.createNgxSseClientStream( this.topicUri );
    }
    
    testSubscribing(): void
    {
        this.authService.generateMercureToken().subscribe( ( jwt: MercureJwt ) => {
            // alert( `MercureToken: ${JSON.stringify( jwt )}` );
            this.openDebugWindow( jwt.token );
            
            this.frontendPublisher.sendToTestSubscribingTopic( this.topicUri, jwt.token ).then( ( data ) => {
                alert( `Publish to Test Subscribing Component. Response: ${JSON.stringify( data )}` );
            });
        });
    }
    
    sendTestMessage(): void
    {
        this.backendPublisher.sendTestMessage( this.topicUri ).subscribe (
            ( e: MessageData ) => {
                console.log( 'Message received: ' + e.message );
                alert( `Message received: ${JSON.stringify( e )}` );
            }
        );
    }
    
    openDebugWindow( debugText: string ): void
    {
        // debugText = 'Test Debug Window';
        this.onOpenDebugWindow.emit( debugText );
    }
}
