import {
    Component,
    Inject,
    Input,
    Output,
    OnInit
} from '@angular/core';
import { Observable, Subscription, map } from 'rxjs';

import { TranslateService } from '@ngx-translate/core';
import {
    IPlayer,
    IMercureAction,
    MercureActionNames,
    GameRoomDto,
    GetTrumps,
    GetAnnounceSymbol,
    CardGameAnnounceSymbolModel,
    ContractBridgeBidDto,
    PlayerPosition,
    PlayerPositions,
    Helper,
    BidTrump,
    JoinGameLobbyActionDto,
    LeaveGameLobbyActionDto
} from '@vankosoft/game-platform';

// App State
import { AppStateService } from '../../../state/app-state.service';

// Services
import { AuthService } from '../../../services/auth.service';
import { GameService } from '../../../services/game.service'
import { EventSourceService } from '../../../services/event-source.service';

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
    
    rooms$: Observable<GameRoomDto[]>;
    roomSubs: Subscription;
    
    showSpinner = true;
    
    gameRooms: GameRoomDto[] | undefined;
    gameCategorySlug: string = 'debug-games';
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'event';
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( GameService ) private gameService: GameService,
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
    }
    
    roomChanged( dto: GameRoomDto[] ): void
    {
        // this.openDebugWindowDialog( `App State Rooms Changed: ${JSON.stringify( dto )}` );
        this.gameRooms  = dto;
    }
    
    loadGameRooms(): void
    {
        this.gameService.loadGameSessionsCategory( this.gameCategorySlug ).subscribe( ( rooms: GameRoomDto[] ) => {
            // this.openDebugWindowDialog( JSON.stringify( rooms ) );
            
            this.showSpinner    = false;
            
            this.gameRooms      = rooms;
            this.appStateService.gameRooms.setValue( rooms );
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
        
        const action: JoinGameLobbyActionDto = {
            actionName: MercureActionNames.joinGameLobby,
            room: room
        };
        
        this.sseService.sendMessage( this.topicUri, JSON.stringify( action ) ).subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
    
    leaveRoom( room: GameRoomDto ): void
    {
        alert( `Leave Room: ${JSON.stringify( room )}` );
        
        const action: LeaveGameLobbyActionDto = {
            actionName: MercureActionNames.leaveGameLobby,
            room: room
        };
        
        this.sseService.sendMessage( this.topicUri, JSON.stringify( action ) ).subscribe( ( message ) => {
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
}