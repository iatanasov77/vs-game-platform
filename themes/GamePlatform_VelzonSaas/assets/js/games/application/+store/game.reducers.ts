import { ActionReducerMap, createReducer, on } from "@ngrx/store";
import {
    loadGameSuccess,
    startGameSuccess,
    playerAnnounceSuccess,
    loadPlayersSuccess,
    loadConnectionsSuccess
} from "./game.actions";

import { IGame } from '../interfaces/game';
import { IPlayer } from '../interfaces/player';
import { IConnection } from '../interfaces/connection';
import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

export interface GameState
{
    game:           null | IGame;
    cardGame:       null | ICardGame;
    announce:       null | ICardGameAnnounce;
    players:        null | IPlayer[];
    connections:    null | IConnection[];
}

const initialState: GameState = {
    game:           null,
    cardGame:       null,
    announce:       null,
    players:        null,
    connections:    null
};

export const gameReducer = createReducer( initialState,
    on( loadGameSuccess, ( state, { game } ) => ( { ...state, game } ) ),
    on( startGameSuccess, ( state, { cardGame } ) => ( { ...state, cardGame } ) ),
    on( playerAnnounceSuccess, ( state, { announce } ) => ( { ...state, announce } ) ),
    on( loadPlayersSuccess, ( state, { players } ) => ( { ...state, players } ) ),
    on( loadConnectionsSuccess, ( state, { connections } ) => ( { ...state, connections } ) ),
);
