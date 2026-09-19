import { Injectable } from '@angular/core';
import { Observable, Subscriber } from 'rxjs';

import { User, Topic, MessageData } from './debug-sse/models';

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
    constructor() { }

    createEventSource( user: User, topic: Topic ): Observable<MessageData>
    {
        const sseUrl            = $( '#TestSsseContainer' ).attr( 'data-mercureEventSource' );
        const topicUri          = topic.getTopic( user );
        
        const eventSourceUrl    = `${sseUrl}?topic=${topicUri}`;
        //const eventSource       = new EventSource( eventSourceUrl );
        const eventSource       = new EventSource( sseUrl );
        
        // alert( `Event Source Url: ${eventSourceUrl}` );
        alert( `Event Source Url: ${sseUrl}` );
        
        return new Observable( observer => {
            eventSource.onmessage = event => {
                const messageData: MessageData = JSON.parse( event.data );
                observer.next( messageData );
            };
        });
   }
}