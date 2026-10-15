import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TestWebsocketRatchetComponent } from './test-websocket-ratchet/test-websocket-ratchet.component';
import { TestWebsocketSwooleComponent } from './test-websocket-swoole/test-websocket-swoole.component';

@NgModule({
    declarations: [
        TestWebsocketRatchetComponent,
        TestWebsocketSwooleComponent,
    ],
    imports: [
        CommonModule
    ],
    exports: [
        TestWebsocketRatchetComponent,
        TestWebsocketSwooleComponent,
    ]
})
export class DebugWebsocketModule { }