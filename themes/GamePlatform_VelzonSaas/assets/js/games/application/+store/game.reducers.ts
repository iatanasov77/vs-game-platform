import { ActionReducerMap, createReducer, on } from "@ngrx/store";

import {
    loadGameSuccess,
    loadPlayersSuccess,
    loadGameRoomsSuccess,
    
    selectGameRoom,
    selectGameRoomSuccess,
    
    startCardGameSuccess
} from "./game.actions";

import IGamePlay from '_@/GamePlatform/Model/GamePlayInterface';

import IGame from '_@/GamePlatform/Model/GameInterface';
import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';

export interface GameState
{
    game:           null | IGame;
    players:        null | IPlayer[];
    rooms:          null | IGameRoom[];
    
    gamePlay:       null | IGamePlay;
}

const initialState: GameState = {
    game:           null,
    players:        null,
    rooms:          null,
    
    gamePlay:       null
};

export const gameReducer = createReducer( initialState,
    on( loadGameSuccess, ( state, { game } ) => ( { ...state, game } ) ),
    on( loadPlayersSuccess, ( state, { players } ) => ( { ...state, players } ) ),
    on( loadGameRoomsSuccess, ( state, { rooms } ) => ( { ...state, rooms } ) ),
    
    on( selectGameRoomSuccess, ( state, { game } ) => ( { ...state, game } ) ),
    on( startCardGameSuccess, ( state, { gamePlay } ) => ( { ...state, gamePlay } ) ),
);
