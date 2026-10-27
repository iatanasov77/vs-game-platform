import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TestSubscribingComponent } from './test-subscribing/test-subscribing.component';
import { TestMultiplayerLobby } from './test-multiplayer-lobby/test-multiplayer-lobby.component';
import { TestUserPaymentComponent } from './test-user-payment/test-user-payment.component';

@NgModule({
    declarations: [
        TestSubscribingComponent,
        TestMultiplayerLobby,
        TestUserPaymentComponent
    ],
    imports: [
        CommonModule
    ],
    exports: [
        TestSubscribingComponent,
        TestMultiplayerLobby,
        TestUserPaymentComponent
    ]
})
export class DebugSseModule { }