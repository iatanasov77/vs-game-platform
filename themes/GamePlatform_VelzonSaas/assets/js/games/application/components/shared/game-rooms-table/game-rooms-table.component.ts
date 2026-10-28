import {
    Component,
    Inject,
    Input,
    Output,
    OnInit
} from '@angular/core';

import { TranslateService } from '@ngx-translate/core';
import { GetTrumps, GetAnnounceSymbol } from '@vankosoft/game-platform';
import { CardGameAnnounceSymbolModel } from '@vankosoft/game-platform';
import { ContractBridgeBidDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';
import { PlayerPositions } from '@vankosoft/game-platform';
import { Helper } from '@vankosoft/game-platform';
import { BidTrump } from '@vankosoft/game-platform';
import { AppStateService } from '../../../state/app-state.service';
import { GameService } from '../../../services/game.service'
import { EventSourceServiceTest } from '../../../services/event-source.service-test';

import { IGameRoom } from '@vankosoft/game-platform';
import { IMercureAction } from '@vankosoft/game-platform';

import cssString from './game-rooms-table.component.scss';
import templateString from './game-rooms-table.component.html';

const { context } = require( '../../../context' );

@Component({
    standalone: false,
    selector: 'game-rooms-table',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class GameRoomsTableComponent implements OnInit
{
    showSpinner = true;
    
    gameRooms: IGameRoom[] | undefined;
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( EventSourceServiceTest ) private sseService: EventSourceServiceTest,
    ) {
        
    }
    
    ngOnInit(): void
    {
        this.loadGameRooms();
        this.subscribeToMercureTopic();
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
    
    subscribeToMercureTopic(): void
    {
        const topicUri = `${context.backendURL}/${window.gamePlatformSettings.gameSlug}`;
        
        this.sseService.createNgxSseClientStream( topicUri );
        
        /*
        const mercureEventSource: string = '';
        this.eventSourceSubscription = this.eventSourceService.createNativeSseClientStream(
            topicUri,
            { withCredentials: false },
            ['GamePlayRoomUpdate']
        )
        
        if ( this.eventSourceSubscription ) {
            this.eventSourceSubscription.subscribe({
                next: ( data: any ) => {
                    let action: IMercureAction  = JSON.parse( data.data );
                    this.updateRooms( action );
                },
                error: ( error: any ) => {
                    console.log( error );
                }
            });
        }
        */
    }
}