import { Injectable, Inject, NgZone } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, Subscriber } from 'rxjs';

import { SseClient } from 'ngx-sse-client';
const EventSource: any = window["EventSource"];
import { EventSourcePolyfill } from "event-source-polyfill";

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
    private eventSource: null | EventSource;
    
    /**
     * constructor
     *
     * @param zone - we need to use zone while working with server-sent events
     * because it's an asynchronous operations which are run outside of change detection scope
     * and we need to notify Angular about changes related to SSE events
     */
    constructor(
        @Inject( NgZone ) private zone: NgZone,
        @Inject( SseClient ) private sseClient: SseClient,
        @Inject( AuthService ) private authService: AuthService,
    ) {
        this.eventSource    = null;
        
        
        /*  
         * Try to Use 'ngx-sse-client'
         *
        const mercureEventSource  = $( '#GameContainer' ).attr( 'data-mercureEventSource' );
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        alert( `SSE Subscribe URL: ${mercureEventSource}` );
        
        this.sseClient.stream(
            mercureEventSource,
            { keepAlive: true, reconnectionDelay: 1_000, responseType: 'event' },
            { headers }, 'POST'
        ).subscribe( ( event ) => {
            alert( `SSE Subscribe Event: ${event.type}` );
            
            if ( event.type === 'error' ) {
                const errorEvent = event as ErrorEvent;
                console.error( errorEvent.error, errorEvent.message );
            } else {
              const messageEvent = event as MessageEvent;
              console.info( `SSE request with type "${messageEvent.type}" and data "${messageEvent.data}"` );
            }
        });
        */
    }
    
    /**
     * Method for establishing connection and subscribing to events from SSE
     *
     * @param eventNames - all event names except error (listens by default) you want to listen to
     */
    connect( url: string, options: EventSourceInit, eventNames: string[] = [] ): Observable<MessageEvent> | undefined
    {
        this.eventSource    = new EventSourcePolyfill( url, options );
        //alert( this.eventSource.url );
        
        return new Observable( ( subscriber: Subscriber<MessageEvent> ) => {
            if ( ! this.eventSource ) {
                return;
            }
            
            this.eventSource.onerror = error => {
                this.zone.run( () => subscriber.error( error ) );
            };
    
            eventNames.forEach( ( event: string ) => {
                if ( ! this.eventSource ) {
                    return;
                }
            
                //alert( event );
                this.eventSource.addEventListener( event, data => {
                    console.log( data.data );
                    this.zone.run( () => subscriber.next( data ) );
                });
            });
        });
    }
    
    /**
     * Method for closing the connection
     */
    close(): void
    {
        if ( ! this.eventSource ) {
            return;
        }
    
        this.eventSource.close();
        this.eventSource = null;
    }
}