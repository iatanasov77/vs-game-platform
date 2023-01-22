import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';

import { BridgeBeloteComponent } from './bridge-belote.component';
import { GameStatisticsComponent } from './components/game-statistics/game-statistics.component';
import { GameBoardComponent } from './components/game-board/game-board.component';
import { PlayerAnnounceComponent } from './components/player-announce/player-announce.component';

@NgModule({
    declarations: [
        BridgeBeloteComponent,
        GameStatisticsComponent,
        GameBoardComponent,
        PlayerAnnounceComponent,
    ],
    imports: [
        BrowserModule,
        
        CommonModule,
        MatTooltipModule,
    ],
    bootstrap: [BridgeBeloteComponent]
})
export class BridgeBeloteModule { }
