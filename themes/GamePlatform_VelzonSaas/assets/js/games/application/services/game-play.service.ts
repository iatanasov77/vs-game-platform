import { Injectable, Inject } from '@angular/core';

import { HttpClient, HttpHeaders } from '@angular/common/http'
const { context } = require( '../context' );

import { Observable, map, of } from 'rxjs';
import { AuthService } from './auth.service';
import { AppConstants } from "../constants";
import { Keys } from '../utils/keys';

import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';
import IGamePlay from '_@/GamePlatform/Model/GamePlayInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import IGame from '_@/GamePlatform/Model/GameInterface';
import { InviteResponseDto } from '../dto/rest/inviteResponseDto';

@Injectable({
    providedIn: 'root'
})
export class GamePlayService
{
    url: string;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService,
    ) {
        this.url    = `${context.backendURL}`;
    }
    
    /**
     * @NOTE This NOT Work Here Because Game Service is Different Instance in API Application From GamePlatform Application
     */
    startPlayGame( gameId: string ): Observable<IGamePlay>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.url}/select-game-room/${gameId}`;
        
        return this.httpClient.get<IGamePlay>( url, {headers} ).pipe(
            map( ( response: any ) => this.mapGamePlay( response ) )
        );
    }
    
    selectGameRoom( inputProps: any ): Observable<IGame>
    {
        //console.log( inputProps );
        return of( inputProps.game ).pipe( map( ( game: IGame ) => ({
            ...game,
            room: inputProps.room
        })));
    }
    
    startGame( game: any ): Observable<IGamePlay>
    {
        if ( ! game ) {
            return new Observable;
        }
        
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.url}/start-game`;
        
        return this.httpClient.post<IGamePlay>( url, {game_room: game.room.id}, {headers} ).pipe(
            map( ( response: any ) => this.mapGamePlay( response ) )
        );
    }
    
    playerAnnounce(): Observable<ICardGameAnnounce>
    {
        let gameId      = 'bridge-belote';
        let announceId  = 'pass';
        
        return new Observable;
    }
    
    finishGame( gamePlay: any ): Observable<IGamePlay>
    {
        if ( ! gamePlay ) {
            return new Observable;
        }
        
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.url}/finish-game`;
        
        return this.httpClient.post<IGamePlay>( url, {game_room: gamePlay.room.id}, {headers} ).pipe(
            map( ( response: any ) => this.mapGamePlay( response ) )
        );
    }
    
    createInvite(): Observable<InviteResponseDto>
    {
        var url = `${this.url}/invite/create`;
        
        return this.httpClient.get<InviteResponseDto>( url ).pipe(
            map( ( dto ) => dto as InviteResponseDto )
        );
    }
    
    private mapGamePlay( response: any ): any
    {
        console.log( 'GamePlay Response: ', response );
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let gamePlay: IGamePlay = {
                id: response.data.id,
                room: response.data.room,
            };
            
            return gamePlay;
        }
        
        return response.message;
    }
}