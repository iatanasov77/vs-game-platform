import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { GameService } from '../../../services/game.service'
import { EventSourceService } from '../../../services/event-source.service';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge, Observable, Subscription } from 'rxjs';

import { IGameRoom } from '@vankosoft/game-platform';
import { IMercureAction } from '@vankosoft/game-platform';

import templateString from './game-rooms.component.html'
import cssString from './game-rooms.component.scss'

declare var $: any;
declare global {
    interface Window {
        gamePlatformSettings: any
    }
}

@Component({
    selector: 'game-rooms',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GameRoomsComponent implements OnInit, OnDestroy
{
    eventSourceSubscription: any;
    
    showSpinner = true;
    
    gameRooms: IGameRoom[] | undefined;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( EventSourceService ) private eventSourceService: EventSourceService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) { }
    
    ngOnInit(): void
    {
        this.loadGameRooms();
        
        let mercureEventSource  = $( '#GameContainer' ).attr( 'data-mercureEventSource' );
        if( ! mercureEventSource ) {
            return;
        }
        
        this.eventSourceSubscription = this.eventSourceService.connectToServerSentEvents(
            mercureEventSource,
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
        if ( this.eventSourceSubscription ) {
            this.eventSourceSubscription.unsubscribe();
            this.eventSourceService.close();
        }
    }
    
    updateRooms( action: IMercureAction ): void
    {
        //console.log( action );
        this.loadGameRooms();
    }
    
    loadGameRooms(): void
    {
        const gameSlug: string =  window.gamePlatformSettings.gameSlug;
        
        this.gameService.loadGameSessions( gameSlug ).subscribe( ( rooms: IGameRoom[] ) => {
            // console.log( rooms );
            // alert( `Game Rooms: ${JSON.stringify( rooms )}` );
            
            this.showSpinner    = false;
            this.gameRooms      = rooms;
        });
    }
}