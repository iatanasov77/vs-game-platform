import { Injectable, Inject } from "@angular/core";

import { createEffect, Actions, ofType } from '@ngrx/effects';
import { catchError, map, switchMap } from "rxjs";

import {
    startGame,
    startGameFailure,
    startGameSuccess,
    
    playerAnnounce,
    playerAnnounceFailure,
    playerAnnounceSuccess
} from "./actions";

import { GameService } from "../services/game.service";
import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';


/**
 * Effects are an RxJS powered side effect model for Store. Effects use streams to provide new sources of actions to reduce state based on external interactions such 
 * as network requests, web socket messages and time-based events.
 * 
 * In a service-based Angular application, components are responsible for interacting with external resources directly through services. Instead, effects provide a way 
 * to interact with those services and isolate them from the components. Effects are where you handle tasks such as fetching data, long-running tasks that produce 
 * multiple events, and other external interactions where your components don't need explicit knowledge of these interactions.
 */

@Injectable({
    providedIn: 'root'
})
export class Effects
{
    constructor(
        @Inject(Actions) private actions$: Actions,
        @Inject(GameService) private gameService: GameService
    ) { }
    
    startGame = createEffect( (): any => this.actions$.pipe(
        ofType( startGame ),
        switchMap( () => this.gameService.startGame().pipe(
            map( ( game: ICardGame ) => startGameSuccess( { game } ) ),
            catchError( error => [startGameFailure( { error } )] )
        ))
    ));
    
    playerAnnounce = createEffect( (): any => this.actions$.pipe(
        ofType( playerAnnounce ),
        switchMap( () => this.gameService.playerAnnounce().pipe(
            map( ( announce: ICardGameAnnounce ) => playerAnnounceSuccess( { announce } ) ),
            catchError( error => [playerAnnounceFailure( { error } )] )
        ))
    ));
}

