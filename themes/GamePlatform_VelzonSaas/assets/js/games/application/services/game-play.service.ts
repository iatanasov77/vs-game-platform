import { Injectable, Inject } from '@angular/core';
import { Observable, map, of } from 'rxjs';
import { Restangular } from 'ngx-restangular';
import { AuthService } from "../services/auth.service";

import IGamePlay from '_@/GamePlatform/Model/GamePlayModel';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

import IGame from '_@/GamePlatform/Model/GameInterface';

@Injectable({
    providedIn: 'root'
})
export class GamePlayService
{
    constructor(
        @Inject( Restangular ) private restangular: Restangular,
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
        
        return this.restangular.all( "start-game" ).customPOST(
            {game_room: game.room.id},
            '',
            {},
            {Authorization: 'Bearer ' + this.authService.getApiToken()}
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
        
        return this.restangular.all( "finish-game" ).customPOST(
            {game_play: gamePlay.id},
            '',
            {},
            {Authorization: 'Bearer ' + this.authService.getApiToken()}
        );
    }
}