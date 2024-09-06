import { Component, OnInit, ElementRef, isDevMode } from '@angular/core';
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
import { GameService } from '../../services/game.service'

import { loadGameBySlug } from '../../+store/game.actions';
import { getGame } from '../../+store/game.selectors';

import { AppConstants } from '../../constants';
const { context } = require( '../../context' );

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
export class GameBaseComponent implements OnInit
{
    isLoggedIn: boolean         = false;
    hasPlayer: boolean          = false;
    developementClass: string   = '';
    currentPlayer: any;
    
    constructor(
        protected elementRef: ElementRef,
        protected authService: AuthService,
        protected gameService: GameService,
        protected store: Store
    ) {
        if( isDevMode() ) {
            this.developementClass  = 'developement';
        }
        
        this.authenticate();
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
        
        this.gameService.hasPlayer().subscribe( ( hasPlayer: boolean ) => {
            //alert( hasPlayer );
            this.hasPlayer = hasPlayer;
        });
    }
    
    authenticate(): void
    {
        if ( window.gamePlatformSettings.apiVerifySiganature.length ) {
            this.store.dispatch( loginBySignature( { apiVerifySiganature: window.gamePlatformSettings.apiVerifySiganature } ) );
            this.store.dispatch( loadGameBySlug( { slug: 'bridge-belote' } ) );
            return;
        }
        
        this.authService.removeAuth();
    }
}
