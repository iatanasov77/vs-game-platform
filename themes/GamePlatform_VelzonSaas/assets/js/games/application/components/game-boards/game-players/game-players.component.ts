import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { EventSourceService } from '../../../services/event-source.service';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';

import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import { IMercureAction } from '../../../interfaces/mercure-action';

import {
    loadPlayers,
    loadPlayersFailure,
    loadPlayersSuccess
} from '../../../+store/game.actions';


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
            //console.log( this.players );
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
        this.eventSourceSubscription.unsubscribe();
        this.eventSourceService.close();
    }
    
    updatePlayers( action: IMercureAction ): void
    {
        //console.log( action );
        this.store.dispatch( loadPlayers() );
    }
}
