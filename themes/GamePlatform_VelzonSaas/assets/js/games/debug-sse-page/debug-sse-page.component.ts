import { Component, OnInit, Inject } from '@angular/core';
import { Observable } from 'rxjs';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Keys } from '@vankosoft/game-platform';
import { CookieService } from 'ngx-cookie-service';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import { AuthService } from '../application/services/auth.service';
import { AppStateService } from '../application/state/app-state.service';

import { ErrorReportDto } from '@vankosoft/game-platform';

import { DebugWindowComponent } from '../application/components/shared/debug-window/debug-window.component';

import cssGameString from './debug-sse-page.component.scss'
import templateString from './debug-sse-page.component.html'

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Component({
    standalone: false,
    selector: 'app-debug-sse-page',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class DebugSsePageComponent implements OnInit
{
    title   = 'Debug SSE';
    errors$: Observable<ErrorState>;
    
    isLoggedIn: boolean = false;
    
    constructor(
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( CookieService ) private cookieService: CookieService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
    ) {
        this.errors$ = this.appState.errors.observe();
        
        if ( ! this.authService.getAuth() && window.gamePlatformSettings.apiVerifySiganature.length ) {
            this.authService.loginBySignature( window.gamePlatformSettings.apiVerifySiganature  ).subscribe( ( auth ) => {
                // alert( `Login By Signature Response: ${JSON.stringify( auth )}` );
                
                /* Not Need This Cookie 
                 *
                let oAuth = this.authService.getAuth();
                let strAuth = '';
                if ( oAuth ) {
                   strAuth =  JSON.stringify( oAuth );
                }
                
                // alert( `Login By Signature Auth: ${strAuth}` );
                this.cookieService.set( Keys.authKey, strAuth, 2 );
                */
            });
        }
    }
    
    ngOnInit(): void {
        this.authService.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            this.isLoggedIn = isLoggedIn;
        });
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
    
    openDebugWindowDialog( debugText: string ): void
    {
        const modalRef = this.ngbModal.open( DebugWindowComponent, { size: 'xl' } );
        modalRef.componentInstance.debugText = debugText;
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            modalRef.dismiss();
        });
    }
}
