import { Injectable, Inject, NgZone } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, Subscriber } from 'rxjs';

import { SseClient } from 'ngx-sse-client';
const EventSource: any = window["EventSource"];
import { EventSourcePolyfill } from "event-source-polyfill";

import { AppConstants } from "../constants";
const { context } = require( '../context' );

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
    }
    
    createNativeSseClientStream( topicUri: string, options: EventSourceInit, eventNames: string[] = [] ): Observable<MessageEvent> | undefined
    {
        const hub           = `${context.mercureHost}?match=${topicUri}`;
        this.eventSource    = new EventSourcePolyfill( hub, options );
        // alert( this.eventSource.url );
        
        return new Observable( ( subscriber: Subscriber<MessageEvent> ) => {
            if ( ! this.eventSource ) return;
            
            this.eventSource.onerror = error => {
                this.zone.run( () => subscriber.error( error ) );
            };
            
            eventNames.forEach( ( event: string ) => {
                if ( ! this.eventSource ) return;
                
                // alert( event );
                this.eventSource.addEventListener( event, data => {
                    console.log( data.data );
                    this.zone.run( () => subscriber.next( data ) );
                });
            });
        });
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
        
        let sseClient = this.createNgxSseClient( topicUri, options );
        this.subscribeNgxSseClientStream( sseClient, responseType );
        
        return sseClient;
    }
    
    private createNgxSseClient( topicUri: string, options: any ): Observable<Event>
    {
        const hub       = `${context.mercureHost}?match=${topicUri}`;
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        let requestOptions = { headers } as const;
        let method = 'POST';
        
        return this.sseClient.stream( hub, options );
    }
    
    private subscribeNgxSseClientStream( sseClient: any, responseType: string ): void
    {
        if ( responseType == 'event' ) {
            // this.sseClient.stream( hub, options, requestOptions, method ).subscribe( ( event ) => {
            sseClient.subscribe( this.handleNgxSseEventResponse.bind( this ) );
        } else {
            sseClient.subscribe( this.handleNgxSseTextResponse.bind( this ) );
        }
    }
    
    private handleNgxSseTextResponse( data: any )
    {
        alert( `NgxSseClient Message: ${data}` );
    }
    
    private handleNgxSseEventResponse( event: any )
    {
        if ( event.type === 'error' ) {
            const errorEvent = event as ErrorEvent;
            
            alert( `NgxSseClient Error: ${errorEvent.message}` );
            console.error( errorEvent.error, errorEvent.message );
        } else {
            const messageEvent = event as MessageEvent;
            
            alert( `NgxSseClient Message: ${messageEvent.data}` );
            console.info( `SSE request with type "${messageEvent.type}" and data "${messageEvent.data}"` );
        }
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