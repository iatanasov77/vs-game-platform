import { Component, OnInit, Inject, ElementRef } from '@angular/core';
import { Store } from '@ngrx/store';

import { AuthService } from '../application/services/auth.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import ICardGameProvider from '../application/interfaces/card-game-provider';
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
    providerBridgeBelote: ICardGameProvider;
    game: BeloteCardGame;
    
    constructor(
        @Inject( ElementRef ) elementRef: ElementRef,
        @Inject( AuthService ) authService: AuthService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store
    ) {
        super( elementRef, authService, gameService, store );
        
        this.providerBridgeBelote   = new BridgeBeloteProvider();
        this.game                   = new BeloteCardGame( this.gameSettings() );
    }
    
    override ngOnInit()
    {
        super.ngOnInit();
    }
}
