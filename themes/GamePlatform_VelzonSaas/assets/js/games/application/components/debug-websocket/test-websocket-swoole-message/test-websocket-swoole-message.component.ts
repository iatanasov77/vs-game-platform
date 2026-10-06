import { Component, OnInit, Inject } from '@angular/core';

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
        @Inject( TestSwooleService ) private wsService: TestSwooleService,
    ) { }
    
    ngOnInit(): void
    {
        this.wsService.connect();
    }
    
    testWebsocketSwoolMessage(): void
    {
        let message: TestMessageDto = {
            message: 'Test Websocket Swool Message !!!'
        };
        this.wsService.sendTestMessage( message );
    }
}
