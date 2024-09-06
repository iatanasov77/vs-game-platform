import { Component, OnInit, Inject, ElementRef } from '@angular/core';
import { Store } from '@ngrx/store';

import { AuthService } from '../application/services/auth.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { BridgeBeloteProvider } from '../application/providers/bridge-belote-provider';
import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';

import cssGameString from './bridge-belote.component.scss'
import templateString from './bridge-belote.component.html'

@Component({
    selector: 'app-bridge-belote',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class BridgeBeloteComponent extends GameBaseComponent implements OnInit
{
    game: BeloteCardGame;
    
    constructor(
        @Inject( ElementRef ) elementRef: ElementRef,
        @Inject( AuthService ) authService: AuthService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
        @Inject( BridgeBeloteProvider ) private providerBridgeBelote: BridgeBeloteProvider
    ) {
        super( elementRef, authService, gameService, store );
        
        this.game   = this.providerBridgeBelote.getGame();
    }
    
    override ngOnInit()
    {
        super.ngOnInit();
    }
}
