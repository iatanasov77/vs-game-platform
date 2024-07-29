import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoaderComponent } from './loader/loader.component';

// Game Boards
import { PlayerAnnounceComponent } from './game-boards/player-announce/player-announce.component';
import { GameStatisticsComponent } from './game-boards/game-statistics/game-statistics.component';
import { CardGameBoardComponent } from './game-boards/card-game-board/card-game-board.component';

@NgModule({
    declarations: [
        LoaderComponent,
        
        // Game Boards
        PlayerAnnounceComponent,
        GameStatisticsComponent,
        CardGameBoardComponent
    ],
    imports: [
        CommonModule
    ],
    exports: [
        LoaderComponent,
        
        // Game Boards
        PlayerAnnounceComponent,
        GameStatisticsComponent,
        CardGameBoardComponent
    ]
})
export class SharedModule { }
