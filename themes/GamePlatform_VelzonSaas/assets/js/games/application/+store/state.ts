import { ActionReducerMap } from '@ngrx/store';
import { routerReducer } from '@ngrx/router-store';

import { GameState, gameReducer } from './game.reducers';

export interface IAppState
{
    main: GameState;
    router: ReturnType<typeof routerReducer>
}

export function getReducers(): ActionReducerMap<IAppState> {
    // map of reducers
    return {
        main: gameReducer,
        router: routerReducer,
    };
}
