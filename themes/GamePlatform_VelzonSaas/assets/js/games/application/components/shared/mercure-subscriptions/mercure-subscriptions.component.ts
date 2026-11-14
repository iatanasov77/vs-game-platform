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
    MercureActionNames,
    GameRoomDto,
    JoinGameLobbyActionDto,
    LeaveGameLobbyActionDto
} from '@vankosoft/game-platform';

// Services
import { AuthService } from '../../../services/auth.service';
import { EventSourceService } from '../../../services/event-source.service';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { DebugWindowComponent } from '../debug-window/debug-window.component';

import { MercureJwt } from '../../../services/debug-sse/models';

import cssString from './mercure-subscriptions.component.scss';
import templateString from './mercure-subscriptions.component.html';

const { context } = require( '../../../context' );

@Component({
    standalone: false,
    selector: 'mercure-subscriptions',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class MercureSubscriptionsComponent implements OnInit
{
    @Input() isLoggedIn: boolean = false;
    @Input() currentPlayer: IPlayer | undefined;
    
    showSpinner = true;
    subscriptions: any;
    
    sseClient$: Observable<Event>;
    topicUri: string;
    sseResponseType: string = 'event';
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( EventSourceService ) private sseService: EventSourceService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
    ) {
        this.topicUri = `${context.backendURL}/test-multiplayer-lobby/${window.gamePlatformSettings.gameSlug}`;
        this.sseClient$ = this.sseService.createNgxSseClientStream( this.topicUri, this.sseResponseType );
    }
    
    ngOnInit(): void
    {
        this.sseService.getMercureSubscriptions().subscribe( ( subscriptions: any ) => {
            this.openDebugWindowDialog( `Mercure Active Subscriptions: ${JSON.stringify( subscriptions )}` );
            
            this.subscriptions = subscriptions;
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