import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, map } from 'rxjs';

import { AuthService } from './auth.service';
import { User, Topic, MessageData } from './debug-sse/models';

import { AppConstants } from "../constants";
const { context } = require( '../context' );

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
export class EventSourceServiceNew
{
    url: string;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService,
    ) {
        // this.url        = `${context.apiURL}`;
        this.url        = `${context.backendURL}`;
    }

    createEventSource( user: User, topic: Topic ): Observable<MessageData>
    {
        const sseUrl            = $( '#TestSsseContainer' ).attr( 'data-mercureEventSource' );
        const eventSource       = new EventSource( sseUrl, { withCredentials: true } );
        
        // alert( `Event Source Url: ${eventSourceUrl}` );
        alert( `Event Source Url: ${sseUrl}` );
        
        return new Observable( observer => {
            eventSource.onmessage = event => {
                alert( `Mercure Recieve Message: ${event.data}` );
                
                const messageData: MessageData = JSON.parse( event.data );
                observer.next( messageData );
            };
        });
    }
    
    connect(): void
    {
        const url = new URL( context.mercureHost );
        url.searchParams.append( "match", "https://example.com/books/1" );
        
        const es = new EventSource( url );
        es.onmessage = ( event ) => {
            /*  
            const li = document.createElement( "li" );
            li.textContent = event.data;
            document.getElementById("log").prepend( li );
            */
        };
    }
   
    sendToTestSubscribingTopic(): Observable<MessageData>
    {
        alert( `sendToTestSubscribingTopic !!!` );
        
        const mercureJwtSecret  = $( '#TestSsseContainer' ).attr( 'data-mercureJwtSecret' );
        const headers           = ( new HttpHeaders() ).set( "Authorization", `${mercureJwtSecret}` );
        
        var url                 = `${this.url}/test-mercure/send-to-test-subscribing-topic`;
        return this.httpClient.get( url, {headers} ).pipe(
            map( ( response: any ) => this.mapMessageData( response ) )
        );
    }
    
    sendToTestMultiplayerLobby(): Observable<MessageData>
    {
        alert( `sendToTestMultiplayerLobby !!!` );
        
        const mercureJwtSecret  = $( '#TestSsseContainer' ).attr( 'data-mercureJwtSecret' );
        const headers           = ( new HttpHeaders() ).set( "Authorization", `${mercureJwtSecret}` );
        
        var url                 = `${this.url}/test-mercure/send-to-test-subscribing-topic`;
        return this.httpClient.get( url, {headers} ).pipe(
            map( ( response: any ) => this.mapMessageData( response ) )
        );
    }
    
    private mapMessageData( response: any )
    {
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let message: MessageData = {
                status: response.status,
                message: response.message,
            };
            
            return message;
        }
        
        return response.message;
    }
}