import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http'
import { Observable} from 'rxjs';

import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

/**
 * Restangular Manual: https://github.com/2muchcoffeecom/ngx-restangular
 */
@Injectable({
    providedIn: 'root'
})
export class GameService
{
    constructor(
        private httpClient: HttpClient,
    ) { }
     
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
