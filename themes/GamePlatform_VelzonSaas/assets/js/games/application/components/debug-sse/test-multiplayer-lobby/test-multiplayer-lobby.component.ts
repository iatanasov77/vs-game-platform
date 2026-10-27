import { Component, OnInit, Inject, Input  } from '@angular/core';

import { MessageData } from '@vankosoft/game-platform';

import { User, PaymentTopic } from '../../../services/debug-sse/models';
import { EventSourceServiceTest } from '../../../services/event-source.service-test'
import { FrontendPublisherService } from '../../../services/debug-sse/frontend-publisher.service'
import { BackendPublisherService } from '../../../services/debug-sse/backend-publisher.service'

import templateString from './test-multiplayer-lobby.component.html';
import styleString from './test-multiplayer-lobby.component.scss';

const { context } = require( '../../../context' );

@Component({
    standalone: false,
    selector: 'app-test-multiplayer-lobby',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestMultiplayerLobby implements OnInit
{
    @Input() isLoggedIn: boolean = false;
    
    topicUri: string;
    
    constructor(
        @Inject( EventSourceServiceTest ) private sseService: EventSourceServiceTest,
        @Inject( FrontendPublisherService ) private frontendPublisher: FrontendPublisherService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
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
    
    testMultiplayerLobby(): void
    {
        /*  
        this.frontendPublisher.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
        */
    }
    
    createLobby(): void
    {
        /*  
        this.frontendPublisher.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
        */
    }
    
    joinLobby(): void
    {
        /*  
        this.frontendPublisher.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
        */
    }
    
    exitLobby(): void
    {
        /*  
        this.frontendPublisher.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
        */
    }
}
