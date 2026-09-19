import { Component, OnInit, Inject } from '@angular/core';
import { Observable, Subscriber } from 'rxjs';

import { EventSourceServiceNew } from '../application/services/event-source.service-new'

import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import { AppStateService } from '../application/state/app-state.service';

import { ErrorReportDto } from '@vankosoft/game-platform';
import { User, PaymentTopic, MessageData } from '../application/services/debug-sse/models';

import cssGameString from './debug-sse.component.scss'
import templateString from './debug-sse.component.html'

@Component({
    selector: 'app-debug-sse',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class DebugSseComponent implements OnInit
{
    title   = 'Debug SSE';
    errors$: Observable<ErrorState>;
  
    constructor(
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( EventSourceServiceNew ) private sseService: EventSourceServiceNew,
    ) {
        this.errors$ = this.appState.errors.observe();
    }
    
    ngOnInit(): void {
        const user: User = {
            id: '65PRG6RD0C87KAQV8RS8H5HHBR',
            name: 'Jose'
        };
        
        const topic = new PaymentTopic();
        this.sseService.createEventSource( user, topic ).subscribe(
            ( e: MessageData ) => {
                console.log( 'DebugSseComponent Message', e.message );
                alert( `Message received: ${e.message}` );
            }
        );
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
}
