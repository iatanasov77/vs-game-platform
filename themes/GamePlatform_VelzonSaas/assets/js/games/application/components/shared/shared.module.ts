import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';

import { LoaderComponent } from './loader/loader.component';

// Dialogs
import { UserNotLoggedInComponent } from './dialogs/not-loggedin-dialog/not-loggedin-dialog.component';

// Game Boards
import { PlayerAnnounceComponent } from './game-boards/player-announce/player-announce.component';
import { GameStatisticsComponent } from './game-boards/game-statistics/game-statistics.component';
import { CardGameBoardComponent } from './game-boards/card-game-board/card-game-board.component';

@NgModule({
    declarations: [
        LoaderComponent,
        
        // Dialogs
        UserNotLoggedInComponent,
        
        // Game Boards
        PlayerAnnounceComponent,
        GameStatisticsComponent,
        CardGameBoardComponent
    ],
    imports: [
        CommonModule,
        TranslateModule.forChild(),
    ],
    exports: [
        LoaderComponent,
        
        // Dialogs
        UserNotLoggedInComponent,
        
        // Game Boards
        PlayerAnnounceComponent,
        GameStatisticsComponent,
        CardGameBoardComponent
    ]
})
export class SharedModule { }
