import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';

import { SharedModule } from '../shared/shared.module';
import { RequirementsDialogComponent } from './requirements-dialog/requirements-dialog.component';
import { SelectGameRoomDialogComponent } from './select-game-room-dialog/select-game-room-dialog.component';
import { CreateGameRoomDialogComponent } from './create-game-room-dialog/create-game-room-dialog.component';
import { PlayAiQuestionComponent } from './play-ai-question/play-ai-question.component';
import { CreateInviteGameDialogComponent } from './create-invite-game-dialog/create-invite-game-dialog.component';

@NgModule({
    declarations: [
        RequirementsDialogComponent,
        SelectGameRoomDialogComponent,
        CreateGameRoomDialogComponent,
        PlayAiQuestionComponent,
        CreateInviteGameDialogComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        FormsModule,
        ReactiveFormsModule,
        SharedModule
    ],
    exports: [
        RequirementsDialogComponent,
        SelectGameRoomDialogComponent,
        CreateGameRoomDialogComponent,
        PlayAiQuestionComponent,
        CreateInviteGameDialogComponent
    ]
})
export class GameDialogsModule { }
