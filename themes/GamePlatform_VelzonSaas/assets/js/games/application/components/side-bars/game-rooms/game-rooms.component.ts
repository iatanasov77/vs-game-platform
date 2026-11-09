import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AppStateService } from '../../../state/app-state.service';
import { GameService } from '../../../services/game.service';
import { EventSourceService } from '../../../services/event-source.service';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge, Observable, Subscription } from 'rxjs';

import { GameRoomDto } from '@vankosoft/game-platform';

import templateString from './game-rooms.component.html';
import cssString from './game-rooms.component.scss';

const { context } = require( '../../../context' );
declare var $: any;
declare global {
    interface Window {
        gamePlatformSettings: any
    }
}

@Component({
    standalone: false,
    selector: 'game-rooms',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GameRoomsComponent implements OnInit, OnDestroy
{
    eventSourceSubscription: any;
    
    showSpinner = true;
    
    gameRooms: GameRoomDto[] | undefined;
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'event';
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( EventSourceService ) private sseService: EventSourceService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) {
        this.topicUri = `${context.backendURL}/${window.gamePlatformSettings.gameSlug}`;
        this.sseClient$ = this.sseService.createNgxSseClientStream( this.topicUri, this.sseResponseType );
    }
    
    ngOnInit(): void
    {
        this.loadGameRooms();
    }
    
    ngOnDestroy(): void
    {
        if ( this.eventSourceSubscription ) {
            this.eventSourceSubscription.unsubscribe();
            // this.sseService.close();
        }
    }
    
    loadGameRooms(): void
    {
        const gameSlug: string =  window.gamePlatformSettings.gameSlug;
        
        this.gameService.loadGameSessions( gameSlug ).subscribe( ( rooms: GameRoomDto[] ) => {
            // console.log( rooms );
            // alert( `Game Rooms: ${JSON.stringify( rooms )}` );
            
            this.showSpinner    = false;
            this.gameRooms      = rooms;
            this.appStateService.gameRooms.setValue( rooms );
        });
    }
}