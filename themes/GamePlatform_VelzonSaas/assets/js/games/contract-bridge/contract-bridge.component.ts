import { Component, OnInit, OnDestroy, Inject } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { TranslateService } from '@ngx-translate/core';
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

import cssGameString from './contract-bridge.component.scss'
import templateString from './contract-bridge.component.html'

declare var $: any;

@Component({
    standalone: false,
    selector: 'app-contract-bridge',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Template Not Loaded !!!',
    ]
})
export class ContractBridgeComponent extends GameBaseComponent implements OnInit, OnDestroy
{
    title   = 'Conract Bridge';
    errors$: Observable<ErrorState>;
    
    lobbyButtonsVisible = true;
    isStarted           = false;
    isPlayAi            = false;
    
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( SoundService ) soundService: SoundService,
        @Inject( GameService ) gameService: GameService,
        @Inject( UserActivityService ) activityService: UserActivityService,
        @Inject( NgbModal ) ngbModal: NgbModal,
        @Inject( TranslateService ) translateService: TranslateService,
        
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        super( authService, soundService, gameService, activityService, ngbModal, translateService );
        
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
