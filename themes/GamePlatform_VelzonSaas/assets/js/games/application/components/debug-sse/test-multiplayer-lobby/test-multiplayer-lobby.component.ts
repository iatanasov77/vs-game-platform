import { Component, OnInit, Inject } from '@angular/core';

import { MessageData } from '@vankosoft/game-platform';

import { User, PaymentTopic } from '../../../services/debug-sse/models';
import { EventSourceServiceTest } from '../../../services/event-source.service-test'

import templateString from './test-multiplayer-lobby.component.html';
import styleString from './test-multiplayer-lobby.component.scss';

@Component({
    standalone: false,
    selector: 'app-test-multiplayer-lobby',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestMultiplayerLobby implements OnInit
{
    constructor(
        @Inject( EventSourceServiceTest ) private sseService: EventSourceServiceTest,
    ) { }
    
    ngOnInit(): void
    {
        // this.sseService.connect();
        /*  */
        const user: User = {
            id: '65PRG6RD0C87KAQV8RS8H5HHBR',
            name: 'Jose'
        };
        
        const topic = new PaymentTopic( `https://topics.domain.com/${user.id}/payments` );
        this.sseService.createEventSource( topic ).subscribe (
            ( e: MessageData ) => {
                console.log( 'Message received: ' + e.message );
                alert( `Message received: ${e.message}` );
            }
        );
        
    }
    
    testMultiplayerLobby(): void
    {
        this.sseService.sendToTestMultiplayerLobby().subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
}
