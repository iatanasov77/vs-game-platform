import { Injectable, Inject, NgZone } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, Subscriber, map } from 'rxjs';
import { Topic, MessageData, IGameRoom } from '@vankosoft/game-platform';

import { AuthService } from '../auth.service';

import { AppConstants } from "../../constants";
const { context } = require( '../../context' );

// Action Interfaces
import { MercureActionNames } from '../../Enum/mercureActionNames';
import { MercureActionDto } from '../../Dto/Actions/mercureActionDto';
import { GameLobbyCreatedActionDto } from '../../Dto/Actions/gameLobbyCreatedActionDto';
import { GameRoomDto } from '../../Dto/gameRoomDto';

import { TestMercureMessageActionDto } from '../../Dto/Actions/testMercureMessageActionDto';
import { CreateGameLobbyActionDto } from '../../Dto/Actions/createGameLobbyActionDto';
import { JoinGameLobbyActionDto } from '../../Dto/Actions/joinGameLobbyActionDto';
import { LeaveGameLobbyActionDto } from '../../Dto/Actions/leaveGameLobbyActionDto';

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
        const action: TestMercureMessageActionDto = {
            actionName: MercureActionNames.testMessage,
            message: {
                message: "Send Test Message."
            }
        };
        return this.sendMessage( topic, JSON.stringify( action ) );
    }
    
    sendToTestMultiplayerLobby( topic: string ): Observable<MessageData>
    {
        const action: TestMercureMessageActionDto = {
            actionName: MercureActionNames.testMessage,
            message: {
                message: "Send To Test Multiplayer Lobby."
            }
        };
        return this.sendMessage( topic, JSON.stringify( action ) );
    }
    
    createMultiplayerLobby( gameType: string, topic: string ): Observable<MessageData>
    {
        const action: CreateGameLobbyActionDto = {
            actionName: MercureActionNames.createGameLobby,
            gameType: gameType,
        };
        return this.sendMessage( topic, JSON.stringify( action ) );
    }
    
    joinMultiplayerLobby( room: GameRoomDto, topic: string  ): Observable<MessageData>
    {
        const action: JoinGameLobbyActionDto = {
            actionName: MercureActionNames.joinGameLobby,
            room: room
        };
        return this.sendMessage( topic, JSON.stringify( action ) );
    }
    
    leaveMultiplayerLobby(  room: GameRoomDto, topic: string  ): Observable<MessageData>
    {
        const action: LeaveGameLobbyActionDto = {
            actionName: MercureActionNames.leaveGameLobby,
            room: room
        };
        return this.sendMessage( topic, JSON.stringify( action ) );
    }
    
    private sendMessage( topic: string, message: string ): Observable<MessageData>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        const url = `${this.url}/test-mercure/test-message`;
        
        return this.httpClient.post<MessageData>( url, { topic : topic, message: message }, {headers} ).pipe(
            map( ( response: any ) => this.mapMessageData( response ) )
        );
    }
    
    private mapMessageData( response: any )
    {
        // alert( `MessageData: ${JSON.stringify( response )}` );
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
