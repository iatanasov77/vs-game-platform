import { ActionReducerMap, createReducer, on } from "@ngrx/store";
import {
    startGameSuccess,
    playerAnnounceSuccess,
    loadGameSuccess
} from "./game.actions";

import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import { IGame } from '../interfaces/game';

export interface GameState
{
    cardGame:   null | ICardGame;
    announce:   null | ICardGameAnnounce;
    game:       null | IGame;
}

const initialState: GameState = {
    cardGame:   null,
    announce:   null,
    game:       null
};

export const gameReducer = createReducer<GameState>( initialState,
    on( startGameSuccess, ( state, { cardGame } ) => {
        return { ...state, cardGame };
    }),
    
    on( playerAnnounceSuccess, ( state, { announce } ) => {
        return { ...state, announce };
    }),
    
    on( loadGameSuccess, ( state, { game } ) => {
        return { ...state, game };
    })
);
