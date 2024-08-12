import { createFeatureSelector, createSelector } from '@ngrx/store'
import { AuthState } from './login.reducers';
import { IAuth } from '../interfaces/auth';

//export const selectAuthState = ( state: AuthState ) => state.auth;
export const selectAuthState = createFeatureSelector<AuthState>( 'loginReducer' );

export const selectAuth = createSelector(
    selectAuthState,
    ( state: AuthState ) => state?.auth
);

export const selectError = createSelector(
    selectAuthState,
    ( state: AuthState ) => state?.error
);

export const selectIsLoading = createSelector(
    selectAuthState,
    ( state: AuthState ) => state?.isLoading
);
