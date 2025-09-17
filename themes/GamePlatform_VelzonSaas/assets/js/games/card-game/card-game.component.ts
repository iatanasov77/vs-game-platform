import { Component, OnInit, Inject } from '@angular/core';
import { Store } from '@ngrx/store';
import { Observable } from 'rxjs';

const { context } = require( '../application/context' );

import { AuthService } from '../application/services/auth.service'
import { SoundService } from '../application/services/sound.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';
import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';

import { AppStateService } from '../application/state/app-state.service';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import ErrorReportDto from '_@/GamePlatform/Model/Core/errorReportDto';

import cssGameString from './card-game.component.scss'
import templateString from './card-game.component.html'

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

/**
 * Test Card Game
 */
@Component({
    selector: 'app-card-game',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class CardGameComponent extends GameBaseComponent implements OnInit
{
    game: BeloteCardGame;
    errors$: Observable<ErrorState>;
    
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( SoundService ) soundService: SoundService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
        
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        super( authService, soundService, gameService, store );
        
        this.errors$    = this.appState.errors.observe();
        
        this.game       = new BeloteCardGame({
            id: 'bridge-belote',
            publicRootPath: context.themeBuildPath,
            boardSelector: '#card-table',
            timeoutBetweenPlayers: window.gamePlatformSettings.timeoutBetweenPlayers,
        });
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
}
