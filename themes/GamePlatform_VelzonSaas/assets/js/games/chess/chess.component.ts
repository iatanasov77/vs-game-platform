import { Component, OnInit, Inject } from '@angular/core';
import { Store } from '@ngrx/store';

import { AuthService } from '../application/services/auth.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { BridgeBeloteProvider } from '../application/providers/bridge-belote-provider';
//import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';

import cssGameString from './chess.component.scss'
import templateString from './chess.component.html'

/**
 * Chess Board Manual: https://www.npmjs.com/package/ngx-chess-board/v/2.2.3?activeTab=readme
 */
@Component({
    selector: 'app-chess',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class ChessComponent extends GameBaseComponent implements OnInit
{
    //game: BeloteCardGame;
    
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
    ) {
        super( authService, gameService, store );
    }
    
    override ngOnInit()
    {
        super.ngOnInit();
    }
}
