import { Injectable, Inject } from '@angular/core';
import { BehaviorSubject, Observable, tap, map, of } from 'rxjs';
import { Restangular } from 'ngx-restangular';
import { AuthService } from './auth.service';

import IGame from '_@/GamePlatform/Model/GameInterface';
import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';

import { AppConstants } from "../constants";

/**
 * Restangular Manual: https://github.com/2muchcoffeecom/ngx-restangular
 */
@Injectable({
    providedIn: 'root'
})
export class GameService
{
    hasPlayer$: BehaviorSubject<boolean>;
    
    constructor(
        @Inject( Restangular ) private restangular: Restangular,
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
        return this.restangular.one( 'games/' + id ).get();
    }
    
    loadGameBySlug( slug: string ): Observable<IGame>
    {
        return this.restangular.one( 'games-ext/' + slug ).customGET( 
            undefined,
            undefined,
            { "Authorization": 'Bearer ' + this.authService.getApiToken() }
        ).pipe(
            map( ( response: any ) => this.mapGame( response ) )
        );
    }
    
    loadGameRooms(): Observable<IGameRoom[]>
    {
        return this.restangular.all( 'rooms' ).customGET( '' );
    }
    
    loadPlayers(): Observable<IPlayer[]>
    {
        return this.restangular.all( 'players' ).customGET( '' );
    }
    
    loadPlayerByUser( userId: number ): Observable<IPlayer>
    {
        return this.restangular.one( 'players-ext/' + userId ).customGET(
            undefined,
            undefined,
            { "Authorization": 'Bearer ' + this.authService.getApiToken() }
        ).pipe(
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
                    
                    return new Observable;
                }
            })
        );
    }
    
    private mapGame( response: any ): IGame | string
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
