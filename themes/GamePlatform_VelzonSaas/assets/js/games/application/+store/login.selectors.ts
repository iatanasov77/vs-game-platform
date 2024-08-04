import { createSelector } from '@ngrx/store'

const selectLogin = ( state: any ) => state.auth;

export const selectAuth = createSelector(
    selectLogin,
    ( state ) => state.auth
);

export const selectError = createSelector(
    selectLogin,
    ( state ) => state.error
);

export const selectIsLoading = createSelector(
    selectLogin,
    ( state ) => state.isLoading
);
