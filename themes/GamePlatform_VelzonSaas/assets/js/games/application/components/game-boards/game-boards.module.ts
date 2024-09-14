import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';

import { SharedModule } from '../shared/shared.module';
import { PlayerAnnounceComponent } from './player-announce/player-announce.component';
import { GameRoomsComponent } from './game-rooms/game-rooms.component';
import { GamePlayersComponent } from './game-players/game-players.component';
import { GameStatisticsComponent } from './game-statistics/game-statistics.component';
import { CardGameBoardComponent } from './card-game-board/card-game-board.component';
import { GameStartComponent } from './board-actions/game-start/game-start.component';
import { CardGameAnnounceComponent } from './board-actions/card-game-announce/card-game-announce.component';

import { BackgammonContainerComponent } from './backgammon-container/backgammon-container.component';
import { BackgammonBoardComponent } from './backgammon-board/backgammon-board.component';
import { DicesComponent } from './dices/dices.component';
import { BoardMenuComponent } from './board-actions/board-menu/board-menu.component';
import { BoardButtonsComponent } from './board-actions/board-buttons/board-buttons.component';

import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { SelectGameRoomDialogComponent } from './select-game-room-dialog/select-game-room-dialog.component';

@NgModule({
    declarations: [
        PlayerAnnounceComponent,
        GameRoomsComponent,
        GamePlayersComponent,
        GameStatisticsComponent,
        CardGameBoardComponent,
        GameStartComponent,
        CardGameAnnounceComponent,
        BackgammonContainerComponent,
        BackgammonBoardComponent,
        DicesComponent,
        BoardMenuComponent,
        BoardButtonsComponent,
        SelectGameRoomDialogComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        SharedModule,
        FormsModule,
        ReactiveFormsModule,
    ],
    exports: [
        PlayerAnnounceComponent,
        GameRoomsComponent,
        GamePlayersComponent,
        GameStatisticsComponent,
        CardGameBoardComponent,
        GameStartComponent,
        CardGameAnnounceComponent,
        BackgammonContainerComponent,
        BackgammonBoardComponent,
        DicesComponent,
        BoardMenuComponent,
        BoardButtonsComponent,
        SelectGameRoomDialogComponent
    ]
})
export class GameBoardsModule { }
