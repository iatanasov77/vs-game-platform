import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { EventSourceService } from '../../../services/event-source.service';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';

import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';
import { IMercureAction } from '../../../interfaces/mercure-action';

import {
    loadGameRooms,
    loadGameRoomsFailure,
    loadGameRoomsSuccess
} from '../../../+store/game.actions';


import templateString from './game-rooms.component.html'
import cssString from './game-rooms.component.scss'

declare var $: any;

@Component({
    selector: 'game-rooms',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GameRoomsComponent implements OnInit, OnDestroy
{
    eventSourceSubscription: any;
    
    showSpinner = true;
    rooms: null | IGameRoom[] = null;
    
    isFetchingRooms$ = merge(
        this.actions$.pipe(
            ofType( loadGameRooms ),
            map( () => true )
        ),
        this.actions$.pipe(
            ofType( loadGameRoomsSuccess ),
            map( () => false )
        ),
        this.actions$.pipe(
            ofType( loadGameRoomsFailure ),
            map( () => false )
        )
    );
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( EventSourceService ) private eventSourceService: EventSourceService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) {
        this.store.dispatch( loadGameRooms() );
        this.store.subscribe( ( state: any ) => {
            this.showSpinner    = state.app.main.rooms == null;
            this.rooms          = state.app.main.rooms;
            //console.log( this.rooms );
        });
    }
    
    ngOnInit(): void
    {
        this.eventSourceSubscription = this.eventSourceService.connectToServerSentEvents(
            $( '#GameContainer' ).attr( 'data-mercureEventSource' ),
            { withCredentials: false },
            ['GamePlayRoomUpdate']
        ).subscribe({
            next: data => {
                let action: IMercureAction  = JSON.parse( data.data );
                this.updateRooms( action );
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
    
    updateRooms( action: IMercureAction ): void
    {
        //console.log( action );
        this.store.dispatch( loadGameRooms() );
    }
}
