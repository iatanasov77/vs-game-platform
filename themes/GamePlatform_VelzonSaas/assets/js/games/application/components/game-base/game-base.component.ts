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
    currentPlayer: any;
    
    userActivity: any;
    userInactive: Subject<any> = new Subject();
    
    constructor(
        protected authService: AuthService,
        protected soundService: SoundService,
        protected gameService: GameService,
    ) {
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
            // console.log('user has been inactive for 3s');
            // alert( 'user has been inactive for 3s' );
        });
    }
    
    ngOnInit()
    {
        this.authService.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
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
    
    ngOnDestroy(): void
    {
        if ( this.authSubs ) {
            this.authSubs.unsubscribe();
        }
    }
    
    setTimeout(): void
    {
        this.userActivity = setTimeout( () => this.userInactive.next( undefined ), 3000 );
    }
    
    @HostListener( 'window:mousemove' )
    refreshUserState()
    {
        clearTimeout( this.userActivity );
        this.setTimeout();
    }
}
