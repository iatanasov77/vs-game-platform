import { Component, OnInit, Inject } from '@angular/core';

import { TestRatchetService } from '../../../services/websocket/test-ratchet.service'
import { TestSwooleService } from '../../../services/websocket/test-swoole.service'
import { TestMessageDto } from '@vankosoft/game-platform';

import templateString from './test-websocket-swoole-message.component.html';
import styleString from './test-websocket-swoole-message.component.scss';

@Component({
    selector: 'app-test-websocket-swoole-message',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestWebsocketSwooleMessageComponent implements OnInit
{
    constructor(
        @Inject( TestRatchetService ) private wsRatchetService: TestRatchetService,
        @Inject( TestSwooleService ) private wsSwooleService: TestSwooleService,
    ) { }
    
    ngOnInit(): void
    {
        this.wsRatchetService.connect();
        this.wsSwooleService.connect();
    }
    
    testWebsocketRatchetMessage(): void
    {
        let message: TestMessageDto = {
            message: 'Test Websocket Ratchet Message !!!'
        };
        this.wsRatchetService.sendTestMessage( message );
    }
    
    testWebsocketSwooleMessage(): void
    {
        let message: TestMessageDto = {
            message: 'Test Websocket Swoole Message !!!'
        };
        this.wsSwooleService.sendTestMessage( message );
    }
}
