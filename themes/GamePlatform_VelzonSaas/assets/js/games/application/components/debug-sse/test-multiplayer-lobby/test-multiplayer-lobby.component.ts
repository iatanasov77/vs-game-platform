import { Component, OnInit, Inject, Input, Output, EventEmitter  } from '@angular/core';

import { MessageData } from '@vankosoft/game-platform';

import { MercureJwt, User, PaymentTopic } from '../../../services/debug-sse/models';
import { AuthService } from '../../../services/auth.service';
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
    @Output() onOpenDebugWindow = new EventEmitter<string>();
    
    topicUri: string;
    
    constructor(
        @Inject( AuthService ) private authService: AuthService,
        @Inject( EventSourceServiceTest ) private sseService: EventSourceServiceTest,
        @Inject( FrontendPublisherService ) private frontendPublisher: FrontendPublisherService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
    ) {
        this.topicUri = `${context.backendURL}/test-multiplayer-lobby/subscribe`;
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
        this.authService.generateMercureToken().subscribe( ( jwt: MercureJwt ) => {
            // alert( `MercureToken: ${JSON.stringify( jwt )}` );
            this.openDebugWindow( jwt.token );
            
            this.frontendPublisher.sendToTestSubscribingTopic( this.topicUri, jwt.token ).then( ( message ) => {
                alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
            });
        });
    }
    
    createLobby(): void
    {
        this.backendPublisher.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
    
    joinLobby(): void
    {
        /*  
        this.backendPublisher.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
        */
    }
    
    exitLobby(): void
    {
        /*  
        this.backendPublisher.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
        */
    }
    
    openDebugWindow( debugText: string ): void
    {
        // debugText = 'Test Debug Window';
        this.onOpenDebugWindow.emit( debugText );
    }
}
