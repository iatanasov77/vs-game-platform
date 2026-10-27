import { Injectable, Inject, NgZone } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, Subscriber, map } from 'rxjs';

import { SseClient } from 'ngx-sse-client';
import { Topic, MessageData } from '@vankosoft/game-platform';

import { AuthService } from './auth.service';
import { User } from './debug-sse/models';

import { AppConstants } from "../constants";
const { context } = require( '../context' );

import { Buffer } from 'buffer';

declare var $: any;

/**
 * Server-Sent Events service
 *
 * MANUALS
 *============================
 * https://dev.to/icolomina/subscribing-to-server-sent-events-with-angular-ee8
 *
 */
@Injectable({
    providedIn: 'root'
})
export class EventSourceServiceTest
{
    private url: string;
    private eventSource: null | EventSource;
    
    constructor(
        @Inject( NgZone ) private zone: NgZone,
        @Inject( SseClient ) private sseClient: SseClient,
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService,
    ) {
        this.url            = `${context.backendURL}`;
        this.eventSource    = null;
    }

    createNativeSseClientStream( topic: Topic ): Observable<MessageData>
    {
        // const sseUrl        = $( '#TestSsseContainer' ).attr( 'data-mercureEventSource' );
        // const eventSource   = new EventSource( sseUrl, { withCredentials: true } );
        // alert( `Event Source Url: ${sseUrl}` );
        
        const topicUri = topic.getTopic();
        const eventSource   = new EventSource( `${context.mercureHost}?match=${topicUri}` );
        
        return new Observable( observer => {
            eventSource.onmessage = ( event: any ) => {
                alert( `Mercure Recieve Message: ${event.data}` );
                
                const messageData: MessageData = JSON.parse( event.data );
                observer.next( messageData );
            };
        });
    }
    
    createNgxSseClientStream( topicUri: string ): void
    {
        // alert( topicUri );
        const hub       = `${context.mercureHost}?match=${topicUri}`;
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        let options = { keepAlive: true, reconnectionDelay: 1000, responseType: 'event' } as const;
        let requestOptions = { headers } as const;
        let method = 'POST';
        
        // this.sseClient.stream( hub, options, requestOptions, method ).subscribe( ( event ) => {
        this.sseClient.stream( hub, options ).subscribe( ( event: any ) => {
            // alert( `SSE Subscribe Event: ${event.type}` );
            
            if ( event.type === 'error' ) {
                const errorEvent = event as ErrorEvent;
                
                // alert( errorEvent.message );
                console.error( errorEvent.error, errorEvent.message );
            } else {
                const messageEvent = event as MessageEvent;
                
                // alert( messageEvent.data );
                console.info( `SSE request with type "${messageEvent.type}" and data "${messageEvent.data}"` );
            }
        });
    }
}