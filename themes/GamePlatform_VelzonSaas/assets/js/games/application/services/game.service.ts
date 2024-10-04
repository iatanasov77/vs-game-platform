import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { BehaviorSubject, Observable, tap, map, of } from 'rxjs';
import { AuthService } from './auth.service';
import { AppConstants } from "../constants";

import IGame from '_@/GamePlatform/Model/GameInterface';
import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';
import IWampAction  from '../interfaces/wamp-action';

@Injectable({
    providedIn: 'root'
})
export class GameService
{
    hasPlayer$: BehaviorSubject<boolean>;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService
    ) {
        this.hasPlayer$ = new BehaviorSubject<boolean>( false );
    }
    
    public hasPlayer(): Observable<boolean>
    {
        return this.hasPlayer$.asObservable();
    }
    
    loadGame( id: number ): Observable<IGame>
    {
        return this.httpClient.get<IGame>( 'games/' + id );
    }
    
    loadGameBySlug( slug: string ): Observable<IGame>
    {
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        return this.httpClient.get<IGame>( 'games-ext/' + slug, {headers} ).pipe(
            map( ( response: any ) => this.mapGame( response ) )
        );
    }
    
    loadGameRooms(): Observable<IGameRoom[]>
    {
        return this.httpClient.get<IGameRoom[]>( 'game-sessions' );
    }
    
    loadPlayers(): Observable<IPlayer[]>
    {
        return this.httpClient.get<IPlayer[]>( 'players' );
    }
    
    loadPlayerByUser( userId: number ): Observable<IPlayer>
    {
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        return this.httpClient.get<IPlayer | string>( 'players-ext/' + userId, {headers} ).pipe(
            map( ( response: any ) => {
                //console.log( response );
                if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
                    let player: IPlayer = {
                        id: response.data.id,
                        type: response.data.type,
                        name: response.data.name,
                        connected: response.data.connected,
                        rooms: [],
                    };
                    
                    localStorage.setItem( 'player', JSON.stringify( player ) );
                    this.hasPlayer$.next( true );
                    
                    return player;
                } else {
                    localStorage.removeItem( 'player' );
                    this.hasPlayer$.next( false );
                    
                    return response.message;
                }
            })
        );
    }
    
    sendMessage( message: string ): void
    {
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        let wampAction: IWampAction = {
            topic: "game",
            action: message,
        };
        
        this.httpClient.post<IWampAction>( 'zmq-message', wampAction, {headers} ).pipe(
            map( ( data: any ) => { return data; } )
        ).subscribe( ( response: IWampAction ) => {
            alert( 'Game Action Response: ' + response.topic );
        });
    }
    
    private mapGame( response: any )
    {
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let game: IGame = {
                id: response.data.id,
                slug: response.data.slug,
                title: response.data.title,
            };
            
            return game;
        }
        
        return response.message;
    }
}
