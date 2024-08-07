import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';
import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';

import { BridgeBeloteProvider } from '../../../../providers/bridge-belote-provider';
import templateString from './card-game-board.component.html'
import styleString from './card-game-board.component.scss'

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';
import {
    startGame,
    startGameFailure,
    startGameSuccess,
    
    playerAnnounce,
    playerAnnounceFailure,
    playerAnnounceSuccess
} from '../../../../+store/actions';
import { runStartGame, runMakeAnnounce } from '../../../../+store/selectors';

declare var $: any;

@Component({
    selector: 'card-game-board',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class CardGameBoardComponent implements OnInit, OnDestroy
{
    @Input() isLoggedIn: boolean        = false;
    @Input() developementClass: string  = '';

    gameStarted: boolean = false;

    providerBridgeBelote: any;
    game: any;
    announceSymbols: any;
    gameAnnounceIcon: any;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
        
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) {
        // DI Not Worked
        this.providerBridgeBelote   = new BridgeBeloteProvider();
        
        this.game                   = new BeloteCardGame( '#card-table', '/build/game-platform-spa' );
        this.gameAnnounceIcon       = null;
        this.announceSymbols        = this.providerBridgeBelote.getAnnounceSymbols();
    }
    
    ngOnInit(): void
    {
        this.game.initBoard();
        this.listenForGameEvents();
        
        $( '#AnnounceContainer' ).hide();
        $( '#GameAnnounce' ).hide();
    }
    
    ngOnDestroy(): void
    {

    }
    
    listenForGameEvents()
    {
        $( "#card-table" ).get( 0 ).addEventListener( GameEvents.GAME_START_EVENT_NAME, ( event: any ) => {
            const { announceId }    = event.detail;
            
            this.gameAnnounceIcon   = this.providerBridgeBelote.getAnnounceSymbol( announceId )?.value;
            
            $( '#AnnounceContainer' ).hide();
            $( '#GameAnnounce' ).show();
        });
    }
    
    onPlayerAnnounce( announceId: any, event: any )
    {
        event.preventDefault();
        
        $( "#BottomPlayer" ).get( 0 ).dispatchEvent(
            new CustomEvent( GameEvents.PLAYER_ANNOUNCE_EVENT_NAME, {
                detail: {
                    announceId: announceId
                },
            })
        );
    }
    
    onPlayerAnnounceNew( announceId: any, event: any )
    {
        this.store.dispatch( playerAnnounce() );
        this.store.subscribe( ( state: any ) => {
            //this.showSpinner    = state.main.latestTablatures == null;
        });
    }
}
