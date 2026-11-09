import {
    Component,
    Inject,
    Input,
    Output,
    OnInit
} from '@angular/core';
import { Observable, Subscription } from 'rxjs';

import { TranslateService } from '@ngx-translate/core';

import {
    GetTrumps,
    GetAnnounceSymbol,
    CardGameAnnounceSymbolModel,
    ContractBridgeBidDto,
    PlayerPosition,
    PlayerPositions,
    Helper,
    BidTrump,
    GameRoomDto
} from '@vankosoft/game-platform';

import { AppStateService } from '../../../state/app-state.service';
import { GameService } from '../../../services/game.service'
import { EventSourceService } from '../../../services/event-source.service';

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
    rooms$: Observable<GameRoomDto[]>;
    roomSubs: Subscription;
    
    showSpinner = true;
    
    gameRooms: GameRoomDto[] | undefined;
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'event';
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( EventSourceService ) private sseService: EventSourceService,
    ) {
        this.topicUri = `${context.backendURL}/${window.gamePlatformSettings.gameSlug}`;
        this.sseClient$ = this.sseService.createNgxSseClientStream( this.topicUri, this.sseResponseType );
        
        this.rooms$ = this.appStateService.gameRooms.observe();
        this.roomSubs = this.appStateService.gameRooms.observe().subscribe( this.roomChanged.bind( this ) );
    }
    
    ngOnInit(): void
    {
        this.loadGameRooms();
    }
    
    roomChanged( dto: GameRoomDto[] ): void
    {
        // this.openDebugWindowDialog( `App State Rooms Changed: ${JSON.stringify( dto )}` );
        this.gameRooms  = dto;
    }
    
    loadGameRooms(): void
    {
        const gameSlug: string =  window.gamePlatformSettings.gameSlug;
        
        this.gameService.loadGameSessions( gameSlug ).subscribe( ( rooms: GameRoomDto[] ) => {
            // console.log( rooms );
            alert( `Game Rooms: ${JSON.stringify( rooms )}` );
            
            this.showSpinner    = false;
            this.gameRooms      = rooms;
            this.appStateService.gameRooms.setValue( rooms );
        });
    }
}