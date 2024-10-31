import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';
import { ReactiveFormsModule } from '@angular/forms';

import { GameChatComponent } from './game-chat/game-chat.component';

@NgModule({
    declarations: [
        GameChatComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        ReactiveFormsModule,
    ],
    exports: [
        GameChatComponent
    ]
})
export class SideBarsModule { }
