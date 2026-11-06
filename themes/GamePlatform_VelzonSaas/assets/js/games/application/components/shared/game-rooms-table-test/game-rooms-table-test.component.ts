import {
    Component,
    Inject,
    Input,
    Output,
    OnInit
} from '@angular/core';
import { Observable, Subscription, map } from 'rxjs';

import { TranslateService } from '@ngx-translate/core';
import { GetTrumps, GetAnnounceSymbol } from '@vankosoft/game-platform';
import { CardGameAnnounceSymbolModel } from '@vankosoft/game-platform';
import { ContractBridgeBidDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';
import { PlayerPositions } from '@vankosoft/game-platform';
import { Helper } from '@vankosoft/game-platform';
import { BidTrump } from '@vankosoft/game-platform';

// App State
import { AppStateService } from '../../../state/app-state.service';

// DTO
import { GameRoomDto } from '../../../Dto/gameRoomDto';

// Services
import { AuthService } from '../../../services/auth.service';
import { GameService } from '../../../services/game.service'
import { EventSourceService } from '../../../services/event-source.service';
import { BackendPublisherService } from '../../../services/debug-sse/backend-publisher.service'

import { IPlayer } from '@vankosoft/game-platform';
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
    @Input() isLoggedIn: boolean = false;
    @Input() currentPlayer: IPlayer | undefined;
    
    rooms$: Observable<IGameRoom[]>;
    roomSubs: Subscription;
    
    showSpinner = true;
    
    gameRooms: IGameRoom[] | undefined;
    gameCategorySlug: string = 'debug-games';
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'event';
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
        @Inject( EventSourceService ) private sseService: EventSourceService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
    ) {
        this.topicUri = `${context.backendURL}/test-multiplayer-lobby/${window.gamePlatformSettings.gameSlug}`;
        this.sseClient$ = this.sseService.createNgxSseClientStream( this.topicUri, this.sseResponseType );
        
        this.rooms$ = this.appStateService.gameRooms.observe();
        this.roomSubs = this.appStateService.gameRooms.observe().subscribe( this.roomChanged.bind( this ) );
    }
    
    ngOnInit(): void
    {
        this.loadGameRooms();
        
        this.sseClient$.subscribe( this.handleSseResponse.bind( this ) );
    }
    
    roomChanged( dto: IGameRoom[] ): void
    {
        let gameRooms = JSON.stringify( dto );
        
        // alert( `App State Rooms Changed: ${gameRooms}` );
        this.openDebugWindowDialog( gameRooms );
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
    
    isCurrentPlayerInRoom( room: GameRoomDto ): boolean
    {
        const res: boolean = room.players.some( player => player.name === this?.currentPlayer?.name );
        
        return res;
    }
    
    joinRoom( room: GameRoomDto ): void
    {
        // alert( `Join Room: ${JSON.stringify( room )}` );
        
        this.backendPublisher.joinMultiplayerLobby( room, this.topicUri ).subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
    
    leaveRoom( room: GameRoomDto ): void
    {
        alert( `Leave Room: ${JSON.stringify( room )}` );
         
        this.backendPublisher.leaveMultiplayerLobby( room, this.topicUri ).subscribe( ( message: any ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
    
    openDebugWindowDialog( debugText: string ): void
    {
        const modalRef = this.ngbModal.open( DebugWindowComponent, { size: 'xl' } );
        modalRef.componentInstance.debugText = debugText;
        
        modalRef.componentInstance.closeModal.subscribe( () => {
            modalRef.dismiss();
        });
    }
    
    handleSseResponse( data: any )
    {
        alert( `GameRoomsTableTest Message: ${data}` );
    }
}