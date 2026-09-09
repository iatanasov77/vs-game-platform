import { Component, OnInit, OnDestroy, isDevMode } from '@angular/core';
import { Observable, Subscription, map } from 'rxjs';

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
}
