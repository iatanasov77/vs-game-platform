import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, Subscription, Subscriber } from 'rxjs';

import { SseClient } from 'ngx-sse-client';

// Action Interfaces
import { MercureActionNames } from '../Enum/mercureActionNames';
import { MercureActionDto } from '../Dto/Actions/mercureActionDto';
import { GameLobbyCreatedActionDto } from '../Dto/Actions/gameLobbyCreatedActionDto';

import { AppConstants } from "../constants";
const { context } = require( '../context' );

import { AppStateService } from '../state/app-state.service';
import { AuthService } from './auth.service';

declare var $: any;

/**
 * Server-Sent Events service
 *
 * MANUALS
 *============================
 * https://medium.com/@andrewkoliaka/implementing-server-sent-events-in-angular-a5e40617cb78
 * https://web.dev/articles/eventsource-basics
 *
 *
 * There is Angular SSE Client
 *=============================
 * ngx-sse-client: https://www.npmjs.com/package/ngx-sse-client
 */
@Injectable({
    providedIn: 'root'
})
export class EventSourceService
{
    private sseClientStream: Observable<Event> | null;
    private sseSubscription: Subscription | null;
    
    /**
     * constructor
     *
     * @param zone - we need to use zone while working with server-sent events
     * because it's an asynchronous operations which are run outside of change detection scope
     * and we need to notify Angular about changes related to SSE events
     */
    constructor(
        @Inject( SseClient ) private sseClient: SseClient,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( AuthService ) private authService: AuthService,
    ) {
        this.sseClientStream    = null;
        this.sseSubscription    = null;
    }
    
    /*  
     * Try to Use 'ngx-sse-client'
     * https://www.npmjs.com/package/ngx-sse-client
     */
    createNgxSseClientStream( topicUri: string, responseType: string ): Observable<Event>
    {
        let options = {
            keepAlive: true,
            reconnectionDelay: 1000,
            responseType: responseType // responseType: 'event'
        } as const;
        
        this.sseClientStream = this.createNgxSseClient( topicUri, options );
        this.sseSubscription = this.subscribeNgxSseClientStream( this.sseClientStream, responseType );
        
        return this.sseClientStream;
    }
    
    private createNgxSseClient( topicUri: string, options: any ): Observable<Event>
    {
        const hub       = `${context.mercureHost}?match=${topicUri}`;
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        let requestOptions = { headers } as const;
        let method = 'POST';
        
        return this.sseClient.stream( hub, options );
    }
    
    private subscribeNgxSseClientStream( sseClient: any, responseType: string ): Subscription
    {
        if ( responseType == 'event' ) {
            return sseClient.subscribe( this.handleNgxSseEventResponse.bind( this ) );
        }
        
        return sseClient.subscribe( this.handleNgxSseTextResponse.bind( this ) );
    }
    
    private handleNgxSseTextResponse( data: any )
    {
        alert( `NgxSseClient Data Message: ${data}` );
    }
    
    private handleNgxSseEventResponse( event: any )
    {
        if ( event.type === 'error' ) {
            const errorEvent = event as ErrorEvent;
            
            // alert( `NgxSseClient Error: ${errorEvent.message}` );
            // console.error( errorEvent.error, errorEvent.message );
        } else {
            const messageEvent = event as MessageEvent;
            this.onMessage( messageEvent );
        }
    }
    
    onMessage( message: MessageEvent<string> ): void
    {
        if ( ! message.data.length  ) {
            return;
        }
        
        // alert( `NgxSseClient Event Message: ${message.data}` );
        // console.info( `SSE request with type "${message.type}" and data "${message.data}"` );
        
        const action = JSON.parse( message.data ) as MercureActionDto;
        switch ( action.actionName ) {
            case MercureActionNames.gameLobbyCreated: {
                const dto = JSON.parse( message.data ) as GameLobbyCreatedActionDto;
                // alert( `NgxSseClient GameRoomDto: ${dto.room}` );
                
                // Can Use: https://ngrx.io/guide/data
                const gameRooms = this.appState.gameRooms.getValue();
                this.appState.gameRooms.setValue( [...gameRooms, dto.room] );
                
                break;
            }
        }
    }
    
    /**
     * Method for closing the connection
     */
    close(): void
    {
        if ( ! this.sseClientStream || ! this.sseSubscription ) {
            return;
        }
    
        this.sseSubscription.unsubscribe();
        this.sseClientStream = null;
    }
}