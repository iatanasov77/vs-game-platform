import { Component, OnInit, Inject, ElementRef, isDevMode } from '@angular/core';
import { Observable, map } from 'rxjs';
import { Store, provideStore } from '@ngrx/store';
import { provideEffects } from '@ngrx/effects';
import Swal from 'sweetalert2'

import { loginBySignature } from '../application/+store/login.actions';
import { selectAuth } from '../application/+store/login.selectors';
import { AuthState } from '../application/+store/login.reducers';
import { AuthService } from '../application/services/auth.service'
import { IAuth } from '../application/interfaces/auth';
import { GameService } from '../application/services/game.service'
import IPlayer from '../application/interfaces/player';

import { loadGameBySlug } from '../application/+store/game.actions';
import { getGame } from '../application/+store/game.selectors';

import { BridgeBeloteProvider } from '../application/providers/bridge-belote-provider';
import ICardGameProvider from '../application/interfaces/card-game-provider';
import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';

import cssGameString from './bridge-belote.component.scss'
import templateString from './bridge-belote.component.html'

import { AppConstants } from '../application/constants';
const { context } = require( '../application/context' );
declare var $: any;

@Component({
    selector: 'app-bridge-belote',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class BridgeBeloteComponent implements OnInit
{
    isLoggedIn: boolean         = false;
    hasPlayer: boolean          = false;
    developementClass: string   = '';
    apiVerifySiganature?: string;
    
    providerBridgeBelote: ICardGameProvider;
    game: BeloteCardGame;
    currentPlayer: any;

    constructor(
        @Inject( ElementRef ) private elementRef: ElementRef,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( Store ) private store: Store
    ) {
        if( isDevMode() ) {
            this.developementClass  = 'developement';
        }
        this.apiVerifySiganature    = this.elementRef.nativeElement.getAttribute( 'apiVerifySiganature' );
        this.authenticate();
        
        // DI Not Worked
        this.providerBridgeBelote   = new BridgeBeloteProvider();
        this.game                   = new BeloteCardGame( 'bridge-belote', context.themeBuildPath ); // , '#card-table'
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
        if ( this.apiVerifySiganature?.length ) {
            this.store.dispatch( loginBySignature( { apiVerifySiganature: this.apiVerifySiganature } ) );
            this.store.dispatch( loadGameBySlug( { slug: 'bridge-belote' } ) );
            return;
        }
        
        this.authService.removeAuth();
    }
}
