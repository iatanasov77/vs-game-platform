import { Component, OnInit, Inject } from '@angular/core';

import { TestRatchetService } from '../../../services/websocket/test-ratchet.service'
import { TestMessageDto } from '@vankosoft/game-platform';

import templateString from './test-websocket-ratchet.component.html';
import styleString from './test-websocket-ratchet.component.scss';

@Component({
    standalone: false,
    selector: 'app-test-websocket-ratchet',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestWebsocketRatchetComponent implements OnInit
{
    ratchetServerExixts: boolean = false;
    
    constructor(
        @Inject( TestRatchetService ) private wsRatchetService: TestRatchetService,
    ) { }
    
    ngOnInit(): void
    {
        if( this.ratchetServerExixts ) {
            this.wsRatchetService.connect();
        }
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
