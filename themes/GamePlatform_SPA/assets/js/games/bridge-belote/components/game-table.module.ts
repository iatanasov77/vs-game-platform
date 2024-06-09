import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { MatTooltipModule } from '@angular/material/tooltip';

import { GameStatisticsComponent } from './game-table/game-statistics/game-statistics.component';
import { GameBoardComponent } from './game-table/game-board/game-board.component';
import { PlayerAnnounceComponent } from './game-table/player-announce/player-announce.component';

@NgModule({
    declarations: [
        GameStatisticsComponent,
        GameBoardComponent,
        PlayerAnnounceComponent,
    ],
    imports: [
        BrowserModule,
        MatTooltipModule,
    ],
    exports: [
        GameStatisticsComponent,
        GameBoardComponent,
        PlayerAnnounceComponent,
    ]
})
export class GameTableModule { }
