import { Component, OnInit, OnDestroy, isDevMode, HostListener } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { TranslateService } from '@ngx-translate/core';
import { Subscription } from 'rxjs';

import { IAuth } from '@vankosoft/game-platform';
import { IPlayer } from '@vankosoft/game-platform';

import { AuthService } from '../../services/auth.service'
import { SoundService } from '../../services/sound.service'
import { GameService } from '../../services/game.service'
import { UserActivityService } from '../../services/user-activity.service'

import { UserMessageDialogComponent } from '../game-dialogs/user-message-dialog/user-message-dialog.component'

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Component({
    standalone: false,
    selector: 'app-game',
    
    template: ``,
    styles: []
})
export class GameBaseComponent implements OnInit, OnDestroy
{
    authSubs: Subscription | undefined;
    
    isLoggedIn: boolean         = false;
    introPlaying: boolean       = false;
    hasPlayer: boolean          = false;
    developementClass: string   = '';
    currentPlayer: IPlayer | undefined;
    
    userInactivityTimeout: number; 
    
    constructor(
        protected authService: AuthService,
        protected soundService: SoundService,
        protected gameService: GameService,
        protected activityService: UserActivityService,
        protected ngbModal: NgbModal,
        protected translateService: TranslateService,
    ) {
        this.userInactivityTimeout = window.gamePlatformSettings.gamePlayerInactivityTimeout;
        
        if( isDevMode() ) {
            this.developementClass  = 'developement';
        }
        
        if ( ! this.authService.getAuth() && window.gamePlatformSettings.apiVerifySiganature.length ) {
            this.authSubs = this.authService.loginBySignature( window.gamePlatformSettings.apiVerifySiganature  ).subscribe( ( auth ) => {
                // alert( `Login By Signature Response: ${JSON.stringify( auth )}` );
            });
        }
    }
    
    ngOnInit(): void
    {
        // alert( 'Game Opened !!!' );
        
        this.authService.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            this.isLoggedIn = isLoggedIn;
            let auth        = this.authService.getAuth();
            
            if ( isLoggedIn && auth ) {
                // alert( 'Auth ID: ' + auth.id );
                this.gameService.loadPlayerByUser( auth.id ).subscribe( ( player: IPlayer ) => {
                    // alert( `Current Player: ${JSON.stringify( player )}` );
                    //console.log( player );
                    this.currentPlayer  = player;
                });
            }
        });
        
        this.gameService.hasPlayer().subscribe( ( hasPlayer: boolean ) => {
            // alert( hasPlayer );
            this.hasPlayer = hasPlayer;
        });
        
        setTimeout( () => {
            this.soundService.isIntroPlaying().subscribe( ( introPlaying: boolean ) => {
                // alert( 'Intro Playing: ' + introPlaying );
                this.introPlaying = introPlaying;
            });
        });
        
        this.activityService.startMonitoringIdleTime( () => {
            // alert( `⚠️ You have been inactive for ${this.userInactivityTimeout / 60000} minute.` );
            const modalRef = this.ngbModal.open( UserMessageDialogComponent );
            
            const message = `${this.translateService.instant( 'dialogs.you_have_been_inactive' )} 
                                ${this.userInactivityTimeout / 60000} 
                                ${this.translateService.instant( 'dialogs.minutes' )}.`;
            const messageQuestion = `${this.translateService.instant( 'dialogs.do_you_want_exit_game' )}`;
                                
            modalRef.componentInstance.message = message;
            modalRef.componentInstance.messageQuestion = messageQuestion;
            modalRef.componentInstance.timeLeft = 30;
            
            modalRef.componentInstance.closeModal.subscribe( () => {
                // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
                modalRef.dismiss();
            });
        }, this.userInactivityTimeout );
    }
    
    ngOnDestroy(): void
    {
        if ( this.authSubs ) {
            this.authSubs.unsubscribe();
        }
        
        this.activityService.stopMonitoring();
    }
}
