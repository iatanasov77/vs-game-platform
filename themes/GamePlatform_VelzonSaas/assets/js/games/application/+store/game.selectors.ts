import { createFeatureSelector, createSelector } from '@ngrx/store'
import { GameState } from './game.reducers';
import { RouterStateUrl } from './router';

const mainSelector                  = createFeatureSelector<GameState>( 'main' );
const routerSelector                = createFeatureSelector<{ state: RouterStateUrl }>( 'router' );

export const getUrl                 = createSelector(
    routerSelector,
    //s => s?.state?.url
    s => s?.state?.url
);
export const getRouteParams         = createSelector(
    routerSelector,
    //s => s?.state?.params
    s => s?.state?.params
);

export const runStartGame           = createSelector(
    mainSelector,
    //s => s.game
    ( s: GameState ) => s?.game
);
export const runMakeAnnounce        = createSelector(
    mainSelector,
    //s => s.announce
    ( s: GameState ) => s?.announce
);

export const getGame                = createSelector(
    mainSelector,
    //s => s.game
    ( s: GameState ) => s?.game
);
