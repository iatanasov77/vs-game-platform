import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';
import { SharedModule } from '../shared/shared.module';

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
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        SharedModule
    ],
    exports: [
        TestSubscribingComponent,
        TestMultiplayerLobby,
        TestUserPaymentComponent
    ]
})
export class DebugSseModule { }