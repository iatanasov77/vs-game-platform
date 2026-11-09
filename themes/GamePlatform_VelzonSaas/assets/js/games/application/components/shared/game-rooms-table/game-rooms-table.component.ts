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
    IPlayer,
    MercureActionNames,
    JoinGameLobbyActionDto,
    LeaveGameLobbyActionDto,
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
    @Input() isLoggedIn: boolean = false;
    @Input() currentPlayer: IPlayer | undefined;
    
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
        this.gameRooms  = dto;
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
    
    isCurrentPlayerInRoom( room: GameRoomDto ): boolean
    {
        const res: boolean = room.players.some( player => player.name === this?.currentPlayer?.name );
        
        return res;
    }
    
    joinRoom( index: number, room: GameRoomDto ): void
    {
        // alert( `Join Room: ${JSON.stringify( room )}` );
        
        const action: JoinGameLobbyActionDto = {
            actionName: MercureActionNames.joinGameLobby,
            index: index,
            room: room
        };
        
        this.sseService.sendMessage( this.topicUri, JSON.stringify( action ) ).subscribe( ( message ) => {
            // alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
    
    leaveRoom( index: number, room: GameRoomDto ): void
    {
        // alert( `Leave Room: ${JSON.stringify( room )}` );
        
        const action: LeaveGameLobbyActionDto = {
            actionName: MercureActionNames.leaveGameLobby,
            index: index,
            room: room
        };
        
        this.sseService.sendMessage( this.topicUri, JSON.stringify( action ) ).subscribe( ( message ) => {
            // alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
}