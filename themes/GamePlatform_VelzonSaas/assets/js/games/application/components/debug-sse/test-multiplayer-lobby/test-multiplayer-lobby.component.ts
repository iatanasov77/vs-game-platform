import { Component, OnInit, Inject, Input, Output, EventEmitter  } from '@angular/core';
import { Observable } from 'rxjs';

import { MessageData } from '@vankosoft/game-platform';

import { MercureJwt, User, PaymentTopic } from '../../../services/debug-sse/models';
import { AuthService } from '../../../services/auth.service';
import { EventSourceService } from '../../../services/event-source.service'
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
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'event';
    
    constructor(
        @Inject( AuthService ) private authService: AuthService,
        @Inject( EventSourceService ) private sseService: EventSourceService,
        @Inject( FrontendPublisherService ) private frontendPublisher: FrontendPublisherService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
    ) {
        this.topicUri = `${context.backendURL}/test-multiplayer-lobby/subscribe`;
        this.sseClient$ = this.sseService.createNgxSseClientStream( this.topicUri, this.sseResponseType );
    }
    
    ngOnInit(): void
    {
        
    }
    
    testMultiplayerLobby(): void
    {
        this.authService.generateMercureToken().subscribe( ( jwt: MercureJwt ) => {
            // alert( `MercureToken: ${JSON.stringify( jwt )}` );
            // this.openDebugWindow( jwt.token );
            
            this.frontendPublisher.sendToTestMultiplayerLobbyTopic( this.topicUri, jwt.token ).subscribe( response => {
                // Return URN (UUID/GUID)
                alert( `TestMultiplayerLobby Response: ${response}` );
            });
        });
    }
    
    createCardGameLobby(): void
    {
        this.backendPublisher.createMultiplayerLobby( 'card_game', this.topicUri ).subscribe( ( message ) => {
            // alert( `Create CardGame Lobby Response: ${JSON.stringify( message )}` );
        });
    }
    
    createBoardGameLobby(): void
    {
        this.backendPublisher.createMultiplayerLobby( 'board_game', this.topicUri ).subscribe( ( message ) => {
            // alert( `Create BoardGame Lobby Response: ${JSON.stringify( message )}` );
        });
    }
    
    openDebugWindow( debugText: string ): void
    {
        // debugText = 'Test Debug Window';
        this.onOpenDebugWindow.emit( debugText );
    }
}
