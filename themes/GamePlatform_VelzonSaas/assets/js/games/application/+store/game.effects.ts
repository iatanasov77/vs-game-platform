import { Injectable, Inject } from "@angular/core";

import { createEffect, Actions, ofType } from '@ngrx/effects';
import { catchError, map, switchMap } from "rxjs";

import {
    startGame,
    startGameFailure,
    startGameSuccess,
    
    playerAnnounce,
    playerAnnounceFailure,
    playerAnnounceSuccess,
    
    loadGame,
    loadGameBySlug,
    loadGameFailure,
    loadGameSuccess,
    
    loadPlayers,
    loadPlayersFailure,
    loadPlayersSuccess
} from "./game.actions";

import { GameService } from "../services/game.service";
import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import { IGame } from '../interfaces/game';
import { IPlayer } from '../interfaces/player';

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
export class GameEffects
{
    constructor(
        @Inject( Actions ) private actions$: Actions,
        @Inject( GameService ) private gameService: GameService
    ) { }
    
    loadGame = createEffect( (): any =>
        this.actions$.pipe(
            ofType( loadGame ),
            switchMap( ( { id } ) =>
                this.gameService.loadGame( id ).pipe(
                    map( ( game: IGame ) => loadGameSuccess( { game } ) ),
                    catchError( error => [loadGameFailure( { error } )] )
                )
            )
        )
    );
    
    loadGameBySlug = createEffect( (): any =>
        this.actions$.pipe(
            ofType( loadGameBySlug ),
            switchMap( ( { slug } ) =>
                this.gameService.loadGameBySlug( slug ).pipe(
                    map( ( game: IGame ) => loadGameSuccess( { game } ) ),
                    catchError( error => [loadGameFailure( { error } )] )
                )
            )
        )
    );
    
    loadPlayers = createEffect( (): any =>
        this.actions$.pipe(
            ofType( loadPlayers ),
            switchMap( () => this.gameService.loadPlayers().pipe(
                map( ( players: IPlayer[] ) => loadPlayersSuccess( { players } ) ),
                catchError( error => [loadPlayersFailure( { error } )] )
            )
        )
    ));
    
    startGame = createEffect( (): any =>
        this.actions$.pipe(
            ofType( startGame ),
            switchMap( ( { game } ) =>
                this.gameService.startGame( game ).pipe(
                    map( ( cardGame: ICardGame ) => startGameSuccess( { cardGame } ) ),
                    catchError( error => [startGameFailure( { error } )] )
                )
            )
        )
    );
    
    playerAnnounce = createEffect( (): any =>
        this.actions$.pipe(
            ofType( playerAnnounce ),
            switchMap( () =>
                this.gameService.playerAnnounce().pipe(
                    map( ( announce: ICardGameAnnounce ) => playerAnnounceSuccess( { announce } ) ),
                    catchError( error => [playerAnnounceFailure( { error } )] )
                )
            )
        )
    );
}

