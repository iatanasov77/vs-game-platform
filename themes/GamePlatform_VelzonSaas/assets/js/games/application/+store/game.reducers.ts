import { ActionReducerMap, createReducer, on } from "@ngrx/store";
import {
    loadGameSuccess,
    startGameSuccess,
    playerAnnounceSuccess,
    loadPlayersSuccess,
    loadGameRoomsSuccess
} from "./game.actions";

import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

import IGame from '../interfaces/game';
import IPlayer from '../interfaces/player';
import IGameRoom from '../interfaces/game-room';

export interface GameState
{
    game:           null | IGame;
    cardGame:       null | ICardGame;
    announce:       null | ICardGameAnnounce;
    players:        null | IPlayer[];
    rooms:          null | IGameRoom[];
}

const initialState: GameState = {
    game:           null,
    cardGame:       null,
    announce:       null,
    players:        null,
    rooms:          null
};

export const gameReducer = createReducer( initialState,
    on( loadGameSuccess, ( state, { game } ) => ( { ...state, game } ) ),
    on( startGameSuccess, ( state, { cardGame } ) => ( { ...state, cardGame } ) ),
    on( playerAnnounceSuccess, ( state, { announce } ) => ( { ...state, announce } ) ),
    on( loadGameRoomsSuccess, ( state, { rooms } ) => ( { ...state, rooms } ) ),
    on( loadPlayersSuccess, ( state, { players } ) => ( { ...state, players } ) ),
);
