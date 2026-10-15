import { Component, OnInit, Inject } from '@angular/core';

import { TestSwooleService } from '../../../services/websocket/test-swoole.service'
import { TestMessageDto } from '@vankosoft/game-platform';

import templateString from './test-websocket-swoole.component.html';
import styleString from './test-websocket-swoole.component.scss';

@Component({
    selector: 'app-test-websocket-swoole',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class TestWebsocketSwooleComponent implements OnInit
{
    constructor(
        @Inject( TestSwooleService ) private wsSwooleService: TestSwooleService,
    ) { }
    
    ngOnInit(): void
    {
        this.wsSwooleService.connect();
    }
    
    testWebsocketSwooleMessage(): void
    {
        // alert( 'Test Websocket Swoole Message Clicked !!!' );
        
        let message: TestMessageDto = {
            message: 'Test Websocket Swoole Message !!!'
        };
        this.wsSwooleService.sendTestMessage( message );
    }
}
