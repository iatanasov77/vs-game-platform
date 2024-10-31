import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';
import { ReactiveFormsModule } from '@angular/forms';

import { SharedModule } from '../shared/shared.module';
import { GameChatComponent } from './game-chat/game-chat.component';
import { GameRoomsComponent } from './game-rooms/game-rooms.component';
import { GamePlayersComponent } from './game-players/game-players.component';
import { GameStatisticsComponent } from './game-statistics/game-statistics.component';

@NgModule({
    declarations: [
        GameChatComponent,
        GameRoomsComponent,
        GamePlayersComponent,
        GameStatisticsComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        ReactiveFormsModule,
        SharedModule
    ],
    exports: [
        GameChatComponent,
        GameRoomsComponent,
        GamePlayersComponent,
        GameStatisticsComponent
    ]
})
export class SideBarsModule { }
