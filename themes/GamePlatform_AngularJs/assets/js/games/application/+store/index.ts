import { ActionReducerMap, createReducer, on } from "@ngrx/store";
import { routerReducer } from '@ngrx/router-store';
import {
    startGameSuccess,
    playerAnnounceSuccess
} from "./actions";

import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

export interface IMainState
{
    game:       null | ICardGame;
    announce:   null | ICardGameAnnounce;
}

interface IAppState
{
    main: IMainState;
    router: ReturnType<typeof routerReducer>
}

const mainInitialState: IMainState = {
    game:       null,
    announce:   null
};

const mainReducer = createReducer<IMainState>(
    mainInitialState,
    
    on( startGameSuccess, ( state, { game } ) => {
      return { ...state, game };
    }),
    
    on( playerAnnounceSuccess, ( state, { announce } ) => {
      return { ...state, announce };
    })
);

export const reducers: ActionReducerMap<IAppState> = {
    main: mainReducer,
    router: routerReducer,
};
