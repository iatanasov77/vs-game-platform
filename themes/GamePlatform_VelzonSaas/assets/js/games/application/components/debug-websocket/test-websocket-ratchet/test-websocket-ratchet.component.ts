import { Component, OnInit, Inject } from '@angular/core';

import { TestRatchetService } from '../../../services/websocket/test-ratchet.service'
import { TestMessageDto } from '@vankosoft/game-platform';

import templateString from './test-websocket-ratchet.component.html';
import styleString from './test-websocket-ratchet.component.scss';

@Component({
    selector: 'app-test-websocket-ratchet',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestWebsocketRatchetComponent implements OnInit
{
    constructor(
        @Inject( TestRatchetService ) private wsRatchetService: TestRatchetService,
    ) { }
    
    ngOnInit(): void
    {
        this.wsRatchetService.connect();
    }
    
    testWebsocketRatchetMessage(): void
    {
        // alert( 'Test Websocket Ratchet Message Clicked !!!' );
        
        let message: TestMessageDto = {
            message: 'Test Websocket Ratchet Message !!!'
        };
        this.wsRatchetService.sendTestMessage( message );
    }
}
