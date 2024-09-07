import { Injectable, Inject, NgZone } from '@angular/core';
import { Observable, Subscriber } from 'rxjs';

const EventSource: any = window["EventSource"];
import { EventSourcePolyfill } from "event-source-polyfill";

/**
 * Server-Sent Events service
 *
 * MANUALS
 *============================
 * https://medium.com/@andrewkoliaka/implementing-server-sent-events-in-angular-a5e40617cb78
 * https://web.dev/articles/eventsource-basics
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
        @Inject( NgZone ) private zone: NgZone
    ) {
        this.eventSource    = null;
    }
    
    /**
     * Method for establishing connection and subscribing to events from SSE
     *
     * @param eventNames - all event names except error (listens by default) you want to listen to
     */
    connectToServerSentEvents( url: string, options: EventSourceInit, eventNames: string[] = [] ): Observable<MessageEvent>
    {
        this.eventSource    = new EventSourcePolyfill( url, options );
        //alert( this.eventSource.url );
        
        return new Observable( ( subscriber: Subscriber<MessageEvent> ) => {
            if ( this.eventSource ) {
                this.eventSource.onerror = error => {
                    this.zone.run( () => subscriber.error( error ) );
                };
            }
    
            eventNames.forEach( ( event: string ) => {
                //alert( event );
                if ( this.eventSource ) {
                    this.eventSource.addEventListener( event, data => {
                        console.log( data.data );
                        this.zone.run( () => subscriber.next( data ) );
                    });
                }
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