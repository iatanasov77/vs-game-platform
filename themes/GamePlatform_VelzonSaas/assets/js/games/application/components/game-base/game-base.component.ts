import { Component, OnInit, OnDestroy, isDevMode } from '@angular/core';
import { Observable, map } from 'rxjs';
import { Store } from '@ngrx/store';
import { provideEffects } from '@ngrx/effects';
import Swal from 'sweetalert2'

import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import { loginBySignature } from '../../+store/login.actions';
import { selectAuth } from '../../+store/login.selectors';
import { AuthState } from '../../+store/login.reducers';
import { AuthService } from '../../services/auth.service'
import { IAuth } from '../../interfaces/auth';
import { SoundService } from '../../services/sound.service'
import { GameService } from '../../services/game.service'

import { loadGameBySlug } from '../../+store/game.actions';
import { getGame } from '../../+store/game.selectors';

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
    isLoggedIn: boolean         = false;
    introPlaying: boolean       = false;
    hasPlayer: boolean          = false;
    developementClass: string   = '';
    currentPlayer: any;
    
    constructor(
        protected authService: AuthService,
        protected soundService: SoundService,
        protected gameService: GameService,
        protected store: Store
    ) {
        if( isDevMode() ) {
            this.developementClass  = 'developement';
        }
        
        //alert( 'Has Auth: ' + this.authService.getAuth() );
        //alert( 'apiVerifySiganature: ' + window.gamePlatformSettings.apiVerifySiganature );
        if ( ! this.authService.getAuth() && window.gamePlatformSettings.apiVerifySiganature.length ) {
            this.store.dispatch( loginBySignature( { apiVerifySiganature: window.gamePlatformSettings.apiVerifySiganature } ) );
            this.store.dispatch( loadGameBySlug( { slug: window.gamePlatformSettings.gameSlug } ) );
        }
    }
    
    ngOnInit()
    {
        this.authService.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            this.isLoggedIn = isLoggedIn;
            let auth        = this.authService.getAuth();
            
            if ( isLoggedIn && auth ) {
                //alert( 'Auth ID: ' + auth.id );
                this.gameService.loadPlayerByUser( auth.id ).subscribe( ( player: IPlayer ) => {
                    //console.log( player );
                    this.currentPlayer  = player;
                });
            }
        });
        
        setTimeout( () => {
            this.soundService.isIntroPlaying().subscribe( ( introPlaying: boolean ) => {
                //alert( 'Intro Playing: ' + introPlaying );
                this.introPlaying = introPlaying;
            });
        });
        
        this.gameService.hasPlayer().subscribe( ( hasPlayer: boolean ) => {
            //alert( hasPlayer );
            this.hasPlayer = hasPlayer;
        });
    }
    
    ngOnDestroy(): void
    {

    }
}
