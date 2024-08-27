import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';

import { LoaderComponent } from './loader/loader.component';

// Dialogs
import { GameRequirementsDialogComponent } from './dialogs/game-requirements-dialog/game-requirements-dialog.component';

// Game Boards
import { PlayerAnnounceComponent } from './game-boards/player-announce/player-announce.component';
import { GameRoomsComponent } from './game-boards/game-rooms/game-rooms.component';
import { GamePlayersComponent } from './game-boards/game-players/game-players.component';
import { GameStatisticsComponent } from './game-boards/game-statistics/game-statistics.component';
import { CardGameBoardComponent } from './game-boards/card-game-board/card-game-board.component';
import { GameStartComponent } from './game-boards/board-actions/game-start/game-start.component';
import { CardGameAnnounceComponent } from './game-boards/board-actions/card-game-announce/card-game-announce.component';

@NgModule({
    declarations: [
        LoaderComponent,
        
        // Dialogs
        GameRequirementsDialogComponent,
        
        // Game Boards
        PlayerAnnounceComponent,
        GameRoomsComponent,
        GamePlayersComponent,
        GameStatisticsComponent,
        CardGameBoardComponent,
        GameStartComponent,
        CardGameAnnounceComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
    ],
    exports: [
        LoaderComponent,
        
        // Dialogs
        GameRequirementsDialogComponent,
        
        // Game Boards
        PlayerAnnounceComponent,
        GameRoomsComponent,
        GamePlayersComponent,
        GameStatisticsComponent,
        CardGameBoardComponent,
        GameStartComponent,
        CardGameAnnounceComponent
    ]
})
export class SharedModule { }
