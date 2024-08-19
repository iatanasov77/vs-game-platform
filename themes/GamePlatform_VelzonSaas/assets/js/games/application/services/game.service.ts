import { Injectable, Inject } from '@angular/core';
import { Observable} from 'rxjs';

import { HttpClient } from '@angular/common/http'
import { Restangular } from 'ngx-restangular';

import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import { AuthService } from './auth.service';

/**
 * Restangular Manual: https://github.com/2muchcoffeecom/ngx-restangular
 */
@Injectable({
    providedIn: 'root'
})
export class GameService
{
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( Restangular ) private restangular: Restangular,
        @Inject( AuthService ) private authService: AuthService
    ) { }
    
    loadGame( id: number )
    {
        return this.restangular.one( 'games/' + id ).get();
    }
    
    loadGameBySlug( slug: string )
    {
        return this.restangular.one( 'games/' + slug )
                    .customGET( 
                        undefined,
                        undefined,
                        {"Authorization": 'Bearer ' + this.authService.getApiToken()}
                    );
    }
    
    startGame(): Observable<ICardGame>
    {
        let gameId  = 'bridge-belote';
        
        return new Observable;
    }
    
    playerAnnounce(): Observable<ICardGameAnnounce>
    {
        let gameId      = 'bridge-belote';
        let announceId  = 'pass';
        
        return new Observable;
    }
}
