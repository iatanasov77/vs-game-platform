import { Component, OnInit, OnDestroy, Inject } from '@angular/core';
import { Observable } from 'rxjs';

import { AuthService } from '../application/services/auth.service'
import { SoundService } from '../application/services/sound.service'
import { GameService } from '../application/services/game.service'
import { UserActivityService } from '../application/services/user-activity.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { AppStateService } from '../application/state/app-state.service';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';

import { ErrorReportDto } from '@vankosoft/game-platform';

import cssGameString from './svara.component.scss'
import templateString from './svara.component.html'

@Component({
    standalone: false,
    selector: 'app-svara',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class SvaraComponent extends GameBaseComponent implements OnInit, OnDestroy
{
    title   = 'Svara';
    errors$: Observable<ErrorState>;
    
    lobbyButtonsVisible = true;
    isStarted           = false;
    isPlayAi            = false;
    
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( SoundService ) soundService: SoundService,
        @Inject( GameService ) gameService: GameService,
        @Inject( UserActivityService ) activityService: UserActivityService,
        
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        super( authService, soundService, gameService, activityService );
        
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
