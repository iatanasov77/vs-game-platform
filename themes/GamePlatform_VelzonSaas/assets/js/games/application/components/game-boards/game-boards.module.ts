import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';

import { SharedModule } from '../shared/shared.module';
import { GameDialogsModule } from '../game-dialogs/game-dialogs.module';

import { PlayerAnnounceComponent } from './player-announce/player-announce.component';
import { GameStartComponent } from './board-actions/game-start/game-start.component';
import { CardGameAnnounceComponent } from './board-actions/card-game-announce/card-game-announce.component';

import { BridgeBeloteContainerComponent } from './bridge-belote-container/bridge-belote-container.component';
import { BridgeBeloteBoardComponent } from './bridge-belote-board/bridge-belote-board.component';
import { BridgeBeloteAnnounceComponent } from './board-actions/bridge-belote-announce/bridge-belote-announce.component';

import { BackgammonContainerComponent } from './backgammon-container/backgammon-container.component';
import { BackgammonBoardComponent } from './backgammon-board/backgammon-board.component';
import { BackgammonBoardButtonsComponent } from './board-actions/backgammon-board-buttons/backgammon-board-buttons.component';
import { DicesComponent } from './dices/dices.component';
import { BoardButtonsComponent } from './board-actions/board-buttons/board-buttons.component';
import { BoardPlayerComponent } from './board-player/board-player.component';
import { BackgammonVariantsComponent } from './game-variants/backgammon/backgammon-variants.component';

import { ChessContainerComponent } from './chess-container/chess-container.component';
import { NgxChessBoardModule } from 'ngx-chess-board';

@NgModule({
    declarations: [
        PlayerAnnounceComponent,
        GameStartComponent,
        CardGameAnnounceComponent,
        
        BridgeBeloteContainerComponent,
        BridgeBeloteBoardComponent,
        BridgeBeloteAnnounceComponent,
        
        BackgammonContainerComponent,
        BackgammonBoardComponent,
        BackgammonBoardButtonsComponent,
        DicesComponent,
        BoardButtonsComponent,
        BoardPlayerComponent,
        BackgammonVariantsComponent,
        ChessContainerComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        SharedModule,
        GameDialogsModule,
        NgxChessBoardModule.forRoot(),
    ],
    exports: [
        PlayerAnnounceComponent,
        GameStartComponent,
        CardGameAnnounceComponent,
        
        BridgeBeloteContainerComponent,
        BridgeBeloteBoardComponent,
        BridgeBeloteAnnounceComponent,
        
        BackgammonContainerComponent,
        BackgammonBoardComponent,
        BackgammonBoardButtonsComponent,
        DicesComponent,
        BoardButtonsComponent,
        BoardPlayerComponent,
        BackgammonVariantsComponent,
        ChessContainerComponent
    ]
})
export class GameBoardsModule { }
