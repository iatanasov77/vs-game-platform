import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TestWebsocketSwooleMessageComponent } from './test-websocket-swoole-message/test-websocket-swoole-message.component';

@NgModule({
    declarations: [
        TestWebsocketSwooleMessageComponent
    ],
    imports: [
        CommonModule
    ],
    exports: [
        TestWebsocketSwooleMessageComponent
    ]
})
export class DebugWebsocketModule { }