import { Injectable, Inject, NgZone } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, Subscriber, map } from 'rxjs';
import { Topic, MessageData } from '@vankosoft/game-platform';

import { AuthService } from '../auth.service';

import { AppConstants } from "../../constants";
const { context } = require( '../../context' );

@Injectable({
    providedIn: 'root'
})
export class BackendPublisherService
{
    private url: string;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService,
    ) {
        this.url = `${context.apiURL}`;
    }
    
    sendTestMessage( topic: string ): Observable<MessageData>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        const url = `${this.url}/test-mercure/test-message`;
        
        return this.httpClient.post<MessageData>( url, { topic : topic }, {headers} ).pipe(
            map( ( response: any ) => this.mapMessageData( response ) )
        );
    }
    
    private mapMessageData( response: any )
    {
        alert( `MessageData: ${JSON.stringify( response )}` );
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
