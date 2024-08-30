import { Injectable } from '@angular/core';
import { Observable, map, of } from 'rxjs';

import IGamePlay from '_@/GamePlatform/Model/GamePlayModel';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

import IGame from '../interfaces/game';

@Injectable({
    providedIn: 'root'
})
export class GamePlayService
{
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
        
        return of( game ).pipe( map( ( game: IGame ) => this.mapGamePlay( game ) ) );
    }
    
    playerAnnounce(): Observable<ICardGameAnnounce>
    {
        let gameId      = 'bridge-belote';
        let announceId  = 'pass';
        
        return new Observable;
    }
    
    private mapGamePlay( game: IGame ): IGamePlay
    {
        let gamePlay: IGamePlay = {
            //id: game.id,
            id: "New Game Play",
            room: game.room,
            players: null
        };
        
        return gamePlay;
    }
}