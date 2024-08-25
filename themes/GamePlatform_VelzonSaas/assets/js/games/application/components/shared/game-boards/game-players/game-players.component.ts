import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { EventSourceService } from '../../../../services/event-source.service';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';

import { IPlayer } from '../../../../interfaces/player';
import { IConnection } from '../../../../interfaces/connection';

import {
    loadPlayers,
    loadPlayersFailure,
    loadPlayersSuccess,
    
    loadConnections,
    loadConnectionsFailure,
    loadConnectionsSuccess
} from '../../../../+store/game.actions';


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
    connections: null | IConnection[] = null;
    
    isFetchingPlayers$ = merge(
        this.actions$.pipe(
            ofType( loadPlayers ),
            map( () => true )
        ),
        this.actions$.pipe(
            ofType( loadPlayersSuccess ),
            map( () => false )
        ),
        this.actions$.pipe(
            ofType( loadPlayersFailure ),
            map( () => false )
        )
    );
    
    isFetchingConnections$ = merge(
        this.actions$.pipe(
            ofType( loadConnections ),
            map( () => true )
        ),
        this.actions$.pipe(
            ofType( loadConnectionsSuccess ),
            map( () => false )
        ),
        this.actions$.pipe(
            ofType( loadConnectionsFailure ),
            map( () => false )
        )
    );
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( EventSourceService ) private eventSourceService: EventSourceService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) {
        this.store.dispatch( loadPlayers() );
        this.store.subscribe( ( state: any ) => {
            this.showSpinner    = state.app.main.players == null;
            this.players        = state.app.main.players;
            this.connections    = state.app.main.connections;
        });
    }
    
    ngOnInit(): void
    {
        this.eventSourceSubscription = this.eventSourceService.connectToServerSentEvents(
            $( '#GameContainer' ).attr( 'data-mercureEventSource' ),
            { withCredentials: false },
            ['activeConnectionUpdate']
        ).subscribe({
            next: data => {
                console.log( data );
            },
            error: error => {
                console.log( error );
            }
        });
    }
    
    ngOnDestroy()
    {
        this.eventSourceSubscription.unsubscribe();
        this.eventSourceService.close();
    }
}
