import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TestSubscribingComponent } from './test-subscribing/test-subscribing.component';
import { TestMultiplayerLobby } from './test-multiplayer-lobby/test-multiplayer-lobby.component';

@NgModule({
    declarations: [
        TestSubscribingComponent,
        TestMultiplayerLobby
    ],
    imports: [
        CommonModule
    ],
    exports: [
        TestSubscribingComponent,
        TestMultiplayerLobby
    ]
})
export class DebugSseModule { }