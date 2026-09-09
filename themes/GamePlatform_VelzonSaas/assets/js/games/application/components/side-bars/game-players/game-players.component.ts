import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { GameService } from '../../../services/game.service'
import { EventSourceService } from '../../../services/event-source.service';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';

import { IPlayer } from '@vankosoft/game-platform';
import { IMercureAction } from '@vankosoft/game-platform';

import templateString from './game-players.component.html'
import cssString from './game-players.component.scss'

declare var $: any;

@Component({
    selector: 'game-players',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GamePlayersComponent implements OnInit, OnDestroy
{
    eventSourceSubscription: any;
    
    showSpinner = true;
    players: null | IPlayer[] = null;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( EventSourceService ) private eventSourceService: EventSourceService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) { }
    
    ngOnInit(): void
    {
        this.loadPlayers();
        
        let mercureEventSource  = $( '#GameContainer' ).attr( 'data-mercureEventSource' );
        if( ! mercureEventSource ) {
            return;
        }
        
        this.eventSourceSubscription = this.eventSourceService.connectToServerSentEvents(
            mercureEventSource,
            { withCredentials: false },
            ['activeConnectionUpdate']
        ).subscribe({
            next: data => {
                let action: IMercureAction  = JSON.parse( data.data );
                this.updatePlayers( action );
            },
            error: error => {
                console.log( error );
            }
        });
    }
    
    ngOnDestroy(): void
    {
        if ( this.eventSourceSubscription ) {
            this.eventSourceSubscription.unsubscribe();
            this.eventSourceService.close();
        }
    }
    
    updatePlayers( action: IMercureAction ): void
    {
        // console.log( action );
        this.loadPlayers();
    }
    
    loadPlayers(): void
    {
        this.gameService.loadPlayers().subscribe( ( players: IPlayer[] ) => {
            // console.log( rooms );
            // alert( `Game Rooms: ${JSON.stringify( players )}` );
            
            this.showSpinner    = false;
            this.players  = players;
        });
    }
}
