import { createFeatureSelector, createSelector } from '@ngrx/store'
import { IMainState } from './index';
import { RouterStateUrl } from './router';

const mainSelector                  = createFeatureSelector<IMainState>( 'main' );
const routerSelector                = createFeatureSelector<{ state: RouterStateUrl }>( 'router' );

export const getUrl                 = createSelector( routerSelector, s => s?.state?.url );
export const getRouteParams         = createSelector( routerSelector, s => s?.state?.params );

export const runStartGame           = createSelector( mainSelector, s => s.game );
export const runMakeAnnounce        = createSelector( mainSelector, s => s.announce );

