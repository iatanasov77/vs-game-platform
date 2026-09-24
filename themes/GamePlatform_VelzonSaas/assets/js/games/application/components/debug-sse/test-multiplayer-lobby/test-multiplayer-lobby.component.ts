import { Component, OnInit, Inject } from '@angular/core';

import { User, PaymentTopic, MessageData } from '../../../services/debug-sse/models';
import { EventSourceServiceNew } from '../../../services/event-source.service-new'

import templateString from './test-multiplayer-lobby.component.html';
import styleString from './test-multiplayer-lobby.component.scss';

@Component({
    selector: 'app-test-multiplayer-lobby',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestMultiplayerLobby implements OnInit
{
    constructor(
        @Inject( EventSourceServiceNew ) private sseService: EventSourceServiceNew,
    ) { }
    
    ngOnInit(): void
    {
        this.sseService.connect();
        /*  
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
        */
    }
    
    testMultiplayerLobby(): void
    {
        this.sseService.sendToTestMultiplayerLobby();
    }
}
