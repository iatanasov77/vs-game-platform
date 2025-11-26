import { Component, OnInit, Inject } from '@angular/core';
import { Store } from '@ngrx/store';
import { Observable } from 'rxjs';

import { AuthService } from '../application/services/auth.service'
import { SoundService } from '../application/services/sound.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { AppStateService } from '../application/state/app-state.service';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import ErrorReportDto from '_@/GamePlatform/Model/Core/errorReportDto';

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
    title   = 'Bridge Belote';
    errors$: Observable<ErrorState>;
    
    lobbyButtonsVisible = true;
    isStarted           = false;
    isPlayAi            = false;
    
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
    }
    
    lobbyButtonsVisibleChanged( value: boolean )
    {
        this.lobbyButtonsVisible = value;
    }
    
    gameIsStarted( value: boolean )
    {
        this.isStarted = value;
    }
    
    gameIsPlayAi( value: boolean )
    {
        this.isPlayAi = value;
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
}
