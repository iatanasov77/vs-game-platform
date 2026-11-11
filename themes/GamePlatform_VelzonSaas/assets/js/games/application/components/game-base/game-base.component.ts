import { Component, OnInit, OnDestroy, isDevMode, HostListener } from '@angular/core';
import { Observable, Subscription, Subject } from 'rxjs';

import { IAuth } from '@vankosoft/game-platform';
import { IPlayer } from '@vankosoft/game-platform';

import { AuthService } from '../../services/auth.service'
import { SoundService } from '../../services/sound.service'
import { GameService } from '../../services/game.service'

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
    
    userActivity: any;
    userInactive: Subject<any> = new Subject();
    userInactivityTimeout: number; 
    
    constructor(
        protected authService: AuthService,
        protected soundService: SoundService,
        protected gameService: GameService,
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
        
        this.setTimeout();
        this.userInactive.subscribe( () => {
            // console.log( `user has been inactive for ${this.userInactivityTimeout / 1000}s` );
            alert( `user has been inactive for ${this.userInactivityTimeout / 1000}s` );
        });
    }
    
    ngOnInit()
    {
        alert( 'Game Opened !!!' );
        
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
    }
    
    // Manual: https://wesleygrimes.com/angular/2019/03/29/making-upgrades-to-angular-ngondestroy
    @HostListener( 'window:beforeunload' )
    async ngOnDestroy()
    {
        if ( this.authSubs ) {
            this.authSubs.unsubscribe();
        }
        
        await this.authService.signOut();
    }
    
    setTimeout(): void
    {
        this.userActivity = setTimeout( () => this.userInactive.next( undefined ), this.userInactivityTimeout );
    }
}
