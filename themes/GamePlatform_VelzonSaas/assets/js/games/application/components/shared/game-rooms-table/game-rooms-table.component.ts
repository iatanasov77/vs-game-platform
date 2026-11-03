import {
    Component,
    Inject,
    Input,
    Output,
    OnInit
} from '@angular/core';
import { Observable } from 'rxjs';

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
import { EventSourceService } from '../../../services/event-source.service';

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
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'text';
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( EventSourceService ) private sseService: EventSourceService,
    ) {
        this.topicUri = `${context.backendURL}/${window.gamePlatformSettings.gameSlug}`;
        this.sseClient$ = this.sseService.createNgxSseClientStream( this.topicUri, this.sseResponseType );
    }
    
    ngOnInit(): void
    {
        this.loadGameRooms();
    }
    
    loadGameRooms(): void
    {
        const gameSlug: string =  window.gamePlatformSettings.gameSlug;
        
        this.gameService.loadGameSessions( gameSlug ).subscribe( ( rooms: IGameRoom[] ) => {
            // console.log( rooms );
            alert( `Game Rooms: ${JSON.stringify( rooms )}` );
            
            this.showSpinner    = false;
            this.gameRooms      = rooms;
        });
    }
}