import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { Observable, map, of } from 'rxjs';
import { AuthService } from './auth.service';
import { AppConstants } from "../constants";

import IGamePlay from '_@/GamePlatform/Model/GamePlayInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import IGame from '_@/GamePlatform/Model/GameInterface';

@Injectable({
    providedIn: 'root'
})
export class GamePlayService
{
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService
    ) { }
    
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
        
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        return this.httpClient.post<IGamePlay>( 'start-game', {game_room: game.room.id}, {headers} ).pipe(
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
        
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        return this.httpClient.post<IGamePlay>( 'finish-game', {game_room: gamePlay.room.id}, {headers} ).pipe(
            map( ( response: any ) => this.mapGamePlay( response ) )
        );
    }
    
    private mapGamePlay( response: any )
    {
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