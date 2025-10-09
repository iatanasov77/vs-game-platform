import { Injectable, Inject } from "@angular/core";

import { createEffect, Actions, ofType } from '@ngrx/effects';
import { switchMap, map, catchError } from "rxjs";

import {
    loadGame,
    loadGameBySlug,
    loadGameFailure,
    loadGameSuccess,
    
    loadPlayers,
    loadPlayersFailure,
    loadPlayersSuccess,
    
    loadGameRooms,
    loadGameRoomsFailure,
    loadGameRoomsSuccess,
    
    selectGameRoom,
    selectGameRoomFailure,
    selectGameRoomSuccess,
    
    startCardGame,
    startCardGameFailure,
    startCardGameSuccess
} from "./game.actions";

import { GameService } from "../services/game.service";
import { GamePlayService } from "../services/game-play.service";
import { EventSourceService } from "../services/event-source.service";

import IGamePlay from '_@/GamePlatform/Model/GamePlayInterface';

import IGame from '_@/GamePlatform/Model/GameInterface';
import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';

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
        @Inject( GameService ) private gameService: GameService,
        @Inject( GamePlayService ) private gamePlayService: GamePlayService,
        @Inject( EventSourceService ) private eventSourceService: EventSourceService
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
    
    loadGameRooms = createEffect( (): any =>
        this.actions$.pipe(
            ofType( loadGameRooms ),
            switchMap( ( { gameSlug } ) =>
                this.gameService.loadGameRooms( gameSlug ).pipe(
                    map( ( rooms: IGameRoom[] ) => loadGameRoomsSuccess( { rooms } ) ),
                    catchError( error => [loadGameRoomsFailure( { error } )] )
                )
            )
        )
    );
    
    loadPlayers = createEffect( (): any =>
        this.actions$.pipe(
            ofType( loadPlayers ),
            switchMap( () =>
                this.gameService.loadPlayers().pipe(
                    map( ( players: IPlayer[] ) => loadPlayersSuccess( { players } ) ),
                    catchError( error => [loadPlayersFailure( { error } )] )
                )
            )
        )
    );
    
    selectGameRoom = createEffect( (): any =>
        this.actions$.pipe(
            ofType( selectGameRoom ),
            switchMap( ( inputProps ) =>
                this.gamePlayService.selectGameRoom( inputProps ).pipe(
                    map( ( game: IGame ) => selectGameRoomSuccess( { game } ) ),
                    catchError( error => [selectGameRoomFailure( { error } )] )
                )
            )
        )
    );
    
    startCardGame = createEffect( (): any =>
        this.actions$.pipe(
            ofType( startCardGame ),
            switchMap( ( { game } ) =>
                this.gamePlayService.startCardGameOld( game ).pipe(
                    map( ( gamePlay: IGamePlay ) => startCardGameSuccess( { gamePlay } ) ),
                    catchError( error => [startCardGameFailure( { error } )] )
                )
            )
        )
    );
}

