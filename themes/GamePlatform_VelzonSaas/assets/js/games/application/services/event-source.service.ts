import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { BehaviorSubject, Observable, Subscription, map } from 'rxjs';

import { SseClient } from 'ngx-sse-client';
import {
    MercureActionNames,
    MessageData,
    MercureMessageDto,
    MercureActionDto,
    GameLobbyCreatedActionDto,
    JoinGameLobbyActionDto,
    LeaveGameLobbyActionDto
} from '@vankosoft/game-platform';

import { AppConstants } from "../constants";
const { context } = require( '../context' );

import { AppStateService } from '../state/app-state.service';
import { AuthService } from './auth.service';
import { MercureJwt } from './debug-sse/models';

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
    private url: string;
    private sseClientStream: Observable<Event> | null;
    private sseSubscription: Subscription | null;
    
    mercureSubscriptions: any;
    mercureSubscriptions$: BehaviorSubject<any>;
    
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
        @Inject( HttpClient ) private httpClient: HttpClient,
    ) {
        this.url = `${context.apiURL}`;
        this.sseClientStream    = null;
        this.sseSubscription    = null;
        
        this.mercureSubscriptions = null;
        this.mercureSubscriptions$  = new BehaviorSubject<any>( this.mercureSubscriptions );
        // this.loadMercureSubscriptions( 'frontend' );
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
    
    sendMessage( topic: string, message: string ): Observable<MessageData>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        const url = `${this.url}/mercure-publish`;
        
        const messageDto:MercureMessageDto = {
            topic : topic,
            message: message
        };
        
        return this.httpClient.post<MessageData>( url, messageDto, {headers} ).pipe(
            map( ( response: any ) => this.mapMessageData( response ) )
        );
    }
    
    getMercureSubscriptions(): Observable<any>
    {
        return this.mercureSubscriptions$.asObservable();
    }
    
    loadMercureSubscriptions( source: string ): void
    {
        if ( source === 'frontend' ) {
            this.authService.generateMercureToken().subscribe( ( jwt: MercureJwt ) => {
                this.loadSubscriptionsFromFrontend( jwt.token ).then( ( data ) => {
                    this.mercureSubscriptions = data;
                    this.mercureSubscriptions$.next( this.mercureSubscriptions );
                });
            });
        } else {
            this.mercureSubscriptions = this.loadMercureSubscriptionsFromBackend();
            this.mercureSubscriptions$.next( this.mercureSubscriptions );
        }
    }
    
    private loadMercureSubscriptionsFromBackend(): Observable<any>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        const url = `${this.url}/mercure-subscriptions`;
        
        return this.httpClient.get( url, {headers} );
    }
    
    /*
     * Manual: https://mercure.rocks/docs/concepts/active-subscriptions#subscription-api
     */
    private async loadSubscriptionsFromFrontend( token: string ): Promise<any>
    {
        let headers = {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/x-www-form-urlencoded",
        };
        
        const response = await fetch( `${context.mercureHost}/subscriptions`, {
            headers: headers,
        });
        
        return response;
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
    
    private onMessage( message: MessageEvent<string> ): void
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
            case MercureActionNames.joinGameLobby: {
                const dto = JSON.parse( message.data ) as JoinGameLobbyActionDto;
                // alert( `NgxSseClient GameRoomDto: ${dto.room}` );
                
                let gameRooms = this.appState.gameRooms.getValue();
                gameRooms.map( ( room ) => room.id == dto.room.id ? dto.room : room );
                
                this.appState.gameRooms.setValue( gameRooms );
                
                break;
            }
            case MercureActionNames.leaveGameLobby: {
                const dto = JSON.parse( message.data ) as LeaveGameLobbyActionDto;
                // alert( `NgxSseClient GameRoomDto: ${dto.room}` );
                
                let gameRooms = this.appState.gameRooms.getValue();
                gameRooms.map( ( room ) => room.id == dto.room.id ? dto.room : room );
                
                this.appState.gameRooms.setValue( gameRooms );
                
                break;
            }
        }
    }
    
    private mapMessageData( response: any )
    {
        // alert( `Map MessageData: ${JSON.stringify( response )}` );
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let message: MessageData = {
                status: response.status,
                message: response.message,
            };
            
            return message;
        }
        
        return response.message;
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