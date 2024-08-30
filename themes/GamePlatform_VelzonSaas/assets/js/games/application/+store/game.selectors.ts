import { createFeatureSelector, createSelector } from '@ngrx/store'
import { GameState } from './game.reducers';
import { RouterStateUrl } from './router';

const mainSelector                  = createFeatureSelector<GameState>( 'main' );
const routerSelector                = createFeatureSelector<{ state: RouterStateUrl }>( 'router' );

import IGameRoom from '../interfaces/game-room';

export const getUrl                 = createSelector(
    routerSelector,
    s => s?.state?.url
);
export const getRouteParams         = createSelector(
    routerSelector,
    s => s?.state?.params
);

export const getGame                = createSelector(
    mainSelector,
    ( s: GameState ) => s?.game
);

export const getPlayers             = createSelector(
    mainSelector,
    ( s: GameState ) => s?.players
);

export const getRooms               = createSelector(
    mainSelector,
    ( s: GameState ) => s?.rooms
);

export const selectGameRoom           = createSelector(
    mainSelector,
    ( s: GameState ) => s?.game
);

export const runStartGame           = createSelector(
    mainSelector,
    ( s: GameState ) => s?.gamePlay
);

export const runMakeAnnounce        = createSelector(
    mainSelector,
    ( s: GameState ) => s?.announce
);
