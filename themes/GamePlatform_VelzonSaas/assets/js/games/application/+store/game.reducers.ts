import { ActionReducerMap, createReducer, on } from "@ngrx/store";
import {
    loadGameSuccess,
    startGameSuccess,
    playerAnnounceSuccess,
    loadPlayersSuccess,
    loadGameRoomsSuccess
} from "./game.actions";

import IGamePlay from '_@/GamePlatform/Model/GamePlayModel';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

import IGame from '../interfaces/game';
import IPlayer from '../interfaces/player';
import IGameRoom from '../interfaces/game-room';

export interface GameState
{
    game:           null | IGame;
    gamePlay:       null | IGamePlay;
    announce:       null | ICardGameAnnounce;
    players:        null | IPlayer[];
    rooms:          null | IGameRoom[];
}

const initialState: GameState = {
    game:           null,
    gamePlay:       null,
    announce:       null,
    players:        null,
    rooms:          null
};

export const gameReducer = createReducer( initialState,
    on( loadGameSuccess, ( state, { game } ) => ( { ...state, game } ) ),
    on( startGameSuccess, ( state, { gamePlay } ) => ( { ...state, gamePlay } ) ),
    on( playerAnnounceSuccess, ( state, { announce } ) => ( { ...state, announce } ) ),
    on( loadGameRoomsSuccess, ( state, { rooms } ) => ( { ...state, rooms } ) ),
    on( loadPlayersSuccess, ( state, { players } ) => ( { ...state, players } ) ),
);
