import { Injectable, Inject } from "@angular/core";

import { createEffect, Actions, ofType } from '@ngrx/effects';
import { switchMap, map, catchError } from "rxjs";

import {
    selectGameRoom,
    selectGameRoomFailure,
    selectGameRoomSuccess,
} from "./game.actions";

import { GameService } from "../services/game.service";
import { GamePlayService } from "../services/game-play.service";
import { EventSourceService } from "../services/event-source.service";

import { IGamePlay } from '@vankosoft/game-platform';

import { IGame } from '@vankosoft/game-platform';
import { IPlayer } from '@vankosoft/game-platform';
import { IGameRoom } from '@vankosoft/game-platform';

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
}

