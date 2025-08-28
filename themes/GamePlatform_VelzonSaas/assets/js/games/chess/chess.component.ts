import { Component, OnInit, Inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Store } from '@ngrx/store';

import { Busy } from '../application/state/busy';

import { AuthService } from '../application/services/auth.service'
import { SoundService } from '../application/services/sound.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { AppStateService } from '../application/state/app-state.service';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import ErrorReportDto from '_@/GamePlatform/Model/Core/errorReportDto';

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
    title   = 'Chess';
    busy$: Observable<Busy>;
    errors$: Observable<ErrorState>;
    
    lobbyButtonsVisible = true;
    playAi              = false;
    forGold             = false;
    
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( SoundService ) soundService: SoundService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
        
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        super( authService, soundService, gameService, store );
        
        this.errors$ = this.appState.errors.observe();
        this.busy$ = this.appState.busy.observe();
        this.authService.repair();
    }
    
    lobbyButtonsVisibleChanged( value: boolean )
    {
        this.lobbyButtonsVisible = value;
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
}
