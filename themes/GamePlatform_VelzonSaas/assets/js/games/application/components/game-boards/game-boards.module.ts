import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';

import { SharedModule } from '../shared/shared.module';
import { GameDialogsModule } from '../game-dialogs/game-dialogs.module';
import { PlayerAnnounceComponent } from './player-announce/player-announce.component';
import { CardGameBoardComponent } from './card-game-board/card-game-board.component';
import { GameStartComponent } from './board-actions/game-start/game-start.component';
import { CardGameAnnounceComponent } from './board-actions/card-game-announce/card-game-announce.component';

import { InviteGameComponent } from './invite-game/invite-game.component';
import { BackgammonContainerComponent } from './backgammon-container/backgammon-container.component';
import { BackgammonBoardComponent } from './backgammon-board/backgammon-board.component';
import { BackgammonBoardButtonsComponent } from './board-actions/backgammon-board-buttons/backgammon-board-buttons.component';
import { DicesComponent } from './dices/dices.component';
import { BoardMenuComponent } from './board-actions/board-menu/board-menu.component';
import { BoardButtonsComponent } from './board-actions/board-buttons/board-buttons.component';
import { BoardPlayerComponent } from './board-player/board-player.component';
import { BackgammonVariantsComponent } from './game-variants/backgammon/backgammon-variants.component';

@NgModule({
    declarations: [
        PlayerAnnounceComponent,
        CardGameBoardComponent,
        GameStartComponent,
        CardGameAnnounceComponent,
        InviteGameComponent,
        BackgammonContainerComponent,
        BackgammonBoardComponent,
        BackgammonBoardButtonsComponent,
        DicesComponent,
        BoardMenuComponent,
        BoardButtonsComponent,
        BoardPlayerComponent,
        BackgammonVariantsComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        SharedModule,
        GameDialogsModule
    ],
    exports: [
        PlayerAnnounceComponent,
        CardGameBoardComponent,
        GameStartComponent,
        CardGameAnnounceComponent,
        InviteGameComponent,
        BackgammonContainerComponent,
        BackgammonBoardComponent,
        BackgammonBoardButtonsComponent,
        DicesComponent,
        BoardMenuComponent,
        BoardButtonsComponent,
        BoardPlayerComponent,
        BackgammonVariantsComponent
    ]
})
export class GameBoardsModule { }
