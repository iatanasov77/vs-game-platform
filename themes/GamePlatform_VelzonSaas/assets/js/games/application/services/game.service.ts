import { Injectable, Inject } from '@angular/core';
import { BehaviorSubject, Observable, tap, map, of } from 'rxjs';

import { HttpClient } from '@angular/common/http'
import { Restangular } from 'ngx-restangular';

import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import { AuthService } from './auth.service';
import { IGame } from '../interfaces/game';
import { IPlayer } from '../interfaces/player';
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
        @Inject( HttpClient ) private httpClient: HttpClient,
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
                        rooms: [],
                        __v: response.data.id
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
    
    startGame( game: any ): Observable<ICardGame>
    {
        if ( ! game ) {
            return new Observable;
        }
        
        return of( game ).pipe( map( ( game: IGame ) => this.mapCardGame( game ) ) );
    }
    
    playerAnnounce(): Observable<ICardGameAnnounce>
    {
        let gameId      = 'bridge-belote';
        let announceId  = 'pass';
        
        return new Observable;
    }
    
    private mapGame( response: any ): IGame | string
    {
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let game: IGame = {
                id: response.data.id,
                slug: response.data.slug,
                title: response.data.title,
                
                __v: 1,
            };
            
            return game;
        }
        
        return response.message;
    }
    
    private mapCardGame( game: IGame ): ICardGame
    {
        let cardGame: ICardGame = {
            //id: game.id,
            deck: game.deck
        };
        
        return cardGame;
    }
}
