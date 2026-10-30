import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, Subscriber, map } from 'rxjs';

import { Topic, MessageData } from '@vankosoft/game-platform';

import { AuthService } from '../auth.service';
import { User } from './models';

import { AppConstants } from "../../constants";
const { context } = require( '../../context' );

declare var $: any;

@Injectable({
    providedIn: 'root'
})
export class FrontendPublisherService
{
    private url: string;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService,
    ) {
        this.url = `${context.backendURL}`;
    }
    
    /**
     * https://mercure.rocks/docs/concepts/publishing#publishing-to-mercure-from-nodejs
     */
    async sendToTestSubscribingTopic( topicUri: string, token: string ): Promise<any>
    {
        let headers = {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/x-www-form-urlencoded",
        };
        
        let body = new URLSearchParams({
            topic: `${topicUri}`,
            data: JSON.stringify( { status: "sent to SubscribingTopic" } ),
        });
        
        const response = await fetch( `${context.mercureHost}`, {
            method: "POST",
            headers: headers,
            body: body,
        });
        
        // alert( `sendToTestMultiplayerLobby URL: ${topicUri}` );
        if ( ! response.ok ) throw new Error( `Publish failed: ${response.status}` );
        
        return response;
    }
    
    /**
     * https://mercure.rocks/docs/concepts/publishing#publishing-to-mercure-from-nodejs
     */
    sendToTestMultiplayerLobbyTopic( topicUri: string, token: string ): Observable<MessageData>
    {
        const headers = ( new HttpHeaders() ).set( "Authorization", `Bearer ${token}` );
        
        var url = `${this.url}/test-mercure/send-to-test-subscribing-topic`;
        alert( `sendToTestMultiplayerLobby URL: ${url}` );
        
        return this.httpClient.get<MessageData>( url, {headers} ).pipe(
            map( ( response: any ) => this.mapMessageData( response ) )
        );
    }
    
    /**
     * https://mercure.rocks/docs/concepts/publishing#publishing-to-mercure-from-nodejs
     */
    sendToTestUserPaymentTopic( token: string ): Observable<MessageData>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", `${token}` );
        
        var url                 = `${this.url}/test-mercure/send-to-test-subscribing-topic`;
        alert( `sendToTestMultiplayerLobby URL: ${url}` );
        
        return this.httpClient.get<MessageData>( url, {headers} ).pipe(
            map( ( response: any ) => this.mapMessageData( response ) )
        );
    }
    
    private mapMessageData( response: any )
    {
        alert( `MessageData: ${response}` );
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
