import { Component, OnInit, Inject } from '@angular/core';
import { Store } from '@ngrx/store';

import { AuthService } from '../application/services/auth.service'
import { SoundService } from '../application/services/sound.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { BridgeBeloteProvider } from '../application/providers/bridge-belote-provider';
//import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';

import cssGameString from './svara.component.scss'
import templateString from './svara.component.html'

@Component({
    selector: 'app-svara',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class SvaraComponent extends GameBaseComponent implements OnInit
{
    //game: BeloteCardGame;
    
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( SoundService ) soundService: SoundService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
    ) {
        super( authService, soundService, gameService, store );
    }
    
    override ngOnInit()
    {
        super.ngOnInit();
    }
}
