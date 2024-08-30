import { ActionReducerMap, createReducer, on } from "@ngrx/store";

import {
    loadGameSuccess,
    loadPlayersSuccess,
    loadGameRoomsSuccess,
    
    selectGameRoom,
    selectGameRoomSuccess,
    
    startGameSuccess,
    playerAnnounceSuccess
} from "./game.actions";

import IGamePlay from '_@/GamePlatform/Model/GamePlayModel';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

import IGame from '../interfaces/game';
import IPlayer from '../interfaces/player';
import IGameRoom from '../interfaces/game-room';

export interface GameState
{
    game:           null | IGame;
    players:        null | IPlayer[];
    rooms:          null | IGameRoom[];
    
    gamePlay:       null | IGamePlay;
    announce:       null | ICardGameAnnounce;
}

const initialState: GameState = {
    game:           null,
    players:        null,
    rooms:          null,
    
    gamePlay:       null,
    announce:       null
};

export const gameReducer = createReducer( initialState,
    on( loadGameSuccess, ( state, { game } ) => ( { ...state, game } ) ),
    on( loadPlayersSuccess, ( state, { players } ) => ( { ...state, players } ) ),
    on( loadGameRoomsSuccess, ( state, { rooms } ) => ( { ...state, rooms } ) ),
    
    on( selectGameRoomSuccess, ( state, { game } ) => ( { ...state, game } ) ),
    on( startGameSuccess, ( state, { gamePlay } ) => ( { ...state, gamePlay } ) ),
    on( playerAnnounceSuccess, ( state, { announce } ) => ( { ...state, announce } ) ),
);
