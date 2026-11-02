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
    
    showSpinner = true;
    
    gameRooms: IGameRoom[] | undefined;
    gameCategorySlug: string = 'debug-games';
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'text';
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( GameService ) private gameService: GameService,
        @Inject( BackendPublisherService ) private backendPublisher: BackendPublisherService,
        @Inject( EventSourceService ) private sseService: EventSourceService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
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
        this.gameService.loadGameSessionsCategory( this.gameCategorySlug ).subscribe( ( rooms: IGameRoom[] ) => {
            // console.log( rooms );
            // alert( `Game Rooms: ${JSON.stringify( rooms )}` );
            // this.openDebugWindowDialog( JSON.stringify( rooms ) );
            
            this.showSpinner    = false;
            this.gameRooms      = rooms;
        });
    }
    
    isCurrentPlayerInRoom( room: IGameRoom ): boolean
    {
        const res: boolean = room.players.some( player => player.name === this?.currentPlayer?.name );
        
        return res;
    }
    
    joinRoom( room: IGameRoom ): void
    {
        // alert( `Join Room: ${JSON.stringify( room )}` );
        
        this.backendPublisher.joinMultiplayerLobby( room ).subscribe( ( message ) => {
            alert( `Test Multiplayer Lobby Component Response: ${JSON.stringify( message )}` );
        });
    }
    
    leaveRoom( room: IGameRoom ): void
    {
        // alert( `Leave Room: ${JSON.stringify( room )}` );
         
        this.backendPublisher.leaveMultiplayerLobby( room ).subscribe( ( message: any ) => {
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