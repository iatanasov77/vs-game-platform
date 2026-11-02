import { Component, OnInit, OnDestroy, Inject } from '@angular/core';
import { Observable, Subscription } from 'rxjs';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { IPlayer } from '@vankosoft/game-platform';

import { Keys } from '@vankosoft/game-platform';
import { CookieService } from 'ngx-cookie-service';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import { AuthService } from '../application/services/auth.service';
import { GameService } from '../application/services/game.service'
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
export class DebugSsePageComponent implements OnInit, OnDestroy
{
    title   = 'Debug SSE';
    errors$: Observable<ErrorState>;
    
    authSubs: Subscription | undefined;
    isLoggedIn: boolean = false;
    currentPlayer: IPlayer | undefined;
    
    constructor(
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( CookieService ) private cookieService: CookieService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
    ) {
        this.errors$ = this.appState.errors.observe();
        
        if ( ! this.authService.getAuth() && window.gamePlatformSettings.apiVerifySiganature.length ) {
            this.authSubs = this.authService.loginBySignature( window.gamePlatformSettings.apiVerifySiganature  ).subscribe( ( auth ) => {
                alert( `Login By Signature Response: ${JSON.stringify( auth )}` );
            });
        }
    }
    
    ngOnInit(): void {
        this.authService.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            // alert( `Logged In: ${isLoggedIn}` );
            this.isLoggedIn = isLoggedIn;
            let auth        = this.authService.getAuth();
            
            if ( isLoggedIn && auth ) {
                // alert( 'Auth ID: ' + auth.id );
                this.gameService.loadPlayerByUser( auth.id ).subscribe( ( player: IPlayer ) => {
                    //console.log( player );
                    this.currentPlayer  = player;
                });
            }
        });
    }
    
    ngOnDestroy(): void
    {
        if ( this.authSubs ) {
            this.authSubs.unsubscribe();
        }
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
