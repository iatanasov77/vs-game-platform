import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';

import { RequirementsDialogComponent } from './requirements-dialog/requirements-dialog.component';
import { SelectGameRoomDialogComponent } from './select-game-room-dialog/select-game-room-dialog.component';
import { CreateGameRoomDialogComponent } from './create-game-room-dialog/create-game-room-dialog.component';
import { PlayAiQuestionComponent } from './play-ai-question/play-ai-question.component';

@NgModule({
    declarations: [
        RequirementsDialogComponent,
        SelectGameRoomDialogComponent,
        CreateGameRoomDialogComponent,
        PlayAiQuestionComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild()
    ],
    exports: [
        RequirementsDialogComponent,
        SelectGameRoomDialogComponent,
        CreateGameRoomDialogComponent,
        PlayAiQuestionComponent
    ]
})
export class GameDialogsModule { }
