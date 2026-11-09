import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';
import { MatButtonToggleModule } from '@angular/material/button-toggle';

import { SharedModule } from '../shared/shared.module';
import { RequirementsDialogComponent } from './requirements-dialog/requirements-dialog.component';
import { GameRoomCreateDialogComponent } from './game-room-create-dialog/game-room-create-dialog.component';
import { GameRoomsDialogComponent } from './game-rooms-dialog/game-rooms-dialog.component';
import { PlayAiQuestionComponent } from './play-ai-question/play-ai-question.component';
import { CreateInviteGameDialogComponent } from './create-invite-game-dialog/create-invite-game-dialog.component';
import { UserLoginDialogComponent } from './user-login-dialog/user-login-dialog.component';
import { ContractBridgeAuctionComponent } from './contract-bridge-auction/contract-bridge-auction.component';
import { DebugGameSoundsComponent } from './debug-game-sounds/debug-game-sounds.component';

@NgModule({
    declarations: [
        RequirementsDialogComponent,
        GameRoomCreateDialogComponent,
        GameRoomsDialogComponent,
        PlayAiQuestionComponent,
        CreateInviteGameDialogComponent,
        UserLoginDialogComponent,
        ContractBridgeAuctionComponent,
        DebugGameSoundsComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        FormsModule,
        ReactiveFormsModule,
        MatButtonToggleModule,
        SharedModule
    ],
    exports: [
        RequirementsDialogComponent,
        GameRoomCreateDialogComponent,
        GameRoomsDialogComponent,
        PlayAiQuestionComponent,
        CreateInviteGameDialogComponent,
        UserLoginDialogComponent,
        ContractBridgeAuctionComponent,
        DebugGameSoundsComponent
    ]
})
export class GameDialogsModule { }
