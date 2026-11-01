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

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { DebugWindowComponent } from '../debug-window/debug-window.component';

import cssString from './game-rooms-table-test.component.scss';
import templateString from './game-rooms-table-test.component.html';

const { context } = require( '../../../context' );

@Component({
    standalone: false,
    selector: 'game-rooms-table-test',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class GameRoomsTableTestComponent implements OnInit
{
    showSpinner = true;
    
    gameRooms: IGameRoom[] | undefined;
    gameCategorySlug: string = 'debug-games';
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( EventSourceServiceTest ) private sseService: EventSourceServiceTest,
        @Inject( NgbModal ) private ngbModal: NgbModal,
    ) {
        
    }
    
    ngOnInit(): void
    {
        this.loadGameRooms();
        this.subscribeToMercureTopic();
    }
    
    loadGameRooms(): void
    {
        this.gameService.loadGameSessionsCategory( this.gameCategorySlug ).subscribe( ( rooms: IGameRoom[] ) => {
            // console.log( rooms );
            // alert( `Game Rooms: ${JSON.stringify( rooms )}` );
            // this.openDebugWindowDialog( JSON.stringify( rooms ) );
            
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
    
    openDebugWindowDialog( debugText: string ): void
    {
        const modalRef = this.ngbModal.open( DebugWindowComponent, { size: 'xl' } );
        modalRef.componentInstance.debugText = debugText;
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            modalRef.dismiss();
        });
    }
}