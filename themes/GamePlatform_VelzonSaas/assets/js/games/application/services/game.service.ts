import { Injectable, Inject } from '@angular/core';

import { HttpClient, HttpHeaders } from '@angular/common/http'
const { context } = require( '../context' );

import { BehaviorSubject, Observable, tap, map, of } from 'rxjs';
import { LocalStorageService } from './local-storage.service';
import { AuthService } from './auth.service';
import { AppConstants } from "../constants";

import IGame from '_@/GamePlatform/Model/GameInterface';
import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';

@Injectable({
    providedIn: 'root'
})
export class GameService
{
    url: string;
    hasPlayer$: BehaviorSubject<boolean>;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( LocalStorageService ) private localStorageService: LocalStorageService,
    ) {
        this.url        = `${context.apiURL}`;
        this.hasPlayer$ = new BehaviorSubject<boolean>( false );
    }
    
    public hasPlayer(): Observable<boolean>
    {
        return this.hasPlayer$.asObservable();
    }
    
    loadGame( id: number ): Observable<IGame>
    {
        var url = `${this.url}/games/${id}`;
        
        return this.httpClient.get<IGame>( url );
    }
    
    loadGameBySlug( slug: string ): Observable<IGame>
    {
        //alert( 'loadGameBySlug Called !' );
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.url}/games-ext/${slug}`;
        
        return this.httpClient.get<IGame>( url, {headers} ).pipe(
            map( ( response: any ) => this.mapGame( response ) )
        );
    }
    
    loadGameVariants( baseGameSlug: string ): Observable<IGame[]>
    {
        var url         = `${this.url}/games-variants/${baseGameSlug}`;
        
        return this.httpClient.get<IGame[]>( url ).pipe(
            map( ( response: any ) => this.mapGames( response ) )
        );
    }
    
    loadGameRooms( gameSlug: string ): Observable<IGameRoom[]>
    {
        //alert( gameSlug );
        var url = `${this.url}/game-sessions/${gameSlug}`;
        
        return this.httpClient.get<IGameRoom[]>( url );
    }
    
    loadPlayers(): Observable<IPlayer[]>
    {
        var url = `${this.url}/players`;
        
        return this.httpClient.get<IPlayer[]>( url );
    }
    
    loadPlayerByUser( userId: number ): Observable<IPlayer>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.url}/players-ext/${userId}`;
        
        return this.httpClient.get<IPlayer | string>( url, {headers} ).pipe(
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
                    
                    this.localStorageService.setItem( 'player', player );
                    this.hasPlayer$.next( true );
                    
                    return player;
                } else {
                    this.localStorageService.removeItem( 'player' );
                    this.hasPlayer$.next( false );
                    
                    return response.message;
                }
            })
        );
    }
    
    private mapGame( response: any )
    {
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let game: IGame = {
                id: response.data.id,
                slug: response.data.slug,
                title: response.data.title,
                url: response.data.url,
            };
            
            return game;
        }
        
        return response.message;
    }
    
    private mapGames( response: any )
    {
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let games: IGame[] = [];
            
            for ( const game of response.data ) {
                games.push({
                    id: game.id,
                    slug: game.slug,
                    title: game.title,
                    url: game.url,
                });
            }
            
            return games;
        }
        
        return response.message;
    }
}
