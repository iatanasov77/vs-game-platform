import { ActionReducerMap, createReducer, on } from "@ngrx/store";
import { routerReducer } from '@ngrx/router-store';
import {
    startGameSuccess,
    playerAnnounceSuccess,
    loadGameSuccess
} from "./actions";

import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import { IGame } from '../interfaces/game';

export interface IMainState
{
    cardGame:   null | ICardGame;
    announce:   null | ICardGameAnnounce;
    game:       null | IGame;
}

interface IAppState
{
    main: IMainState;
    router: ReturnType<typeof routerReducer>
}

const mainInitialState: IMainState = {
    cardGame:   null,
    announce:   null,
    game:       null
};

const mainReducer = createReducer<IMainState>(
    mainInitialState,
    
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

export const reducers: ActionReducerMap<IAppState> = {
    main: mainReducer,
    router: routerReducer,
};
