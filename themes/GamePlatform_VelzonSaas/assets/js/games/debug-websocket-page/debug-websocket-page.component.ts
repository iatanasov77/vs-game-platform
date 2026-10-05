import { Component, OnInit, Inject } from '@angular/core';
import { Observable } from 'rxjs';

import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import { AppStateService } from '../application/state/app-state.service';

import { ErrorReportDto } from '@vankosoft/game-platform';

import cssGameString from './debug-websocket-page.component.scss'
import templateString from './debug-websocket-page.component.html'

@Component({
    selector: 'app-debug-websocket-page',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class DebugWebsocketPageComponent implements OnInit
{
    title   = 'Debug Websocket';
    errors$: Observable<ErrorState>;
  
    constructor(
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService,
    ) {
        this.errors$ = this.appState.errors.observe();
    }
    
    ngOnInit(): void {
        
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
}
