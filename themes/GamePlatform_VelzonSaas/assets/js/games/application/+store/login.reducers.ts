import { createReducer, on } from "@ngrx/store";

import {
    loginBySignature,
    loginBySignatureSuccess,
    loginBySignatureFailure
} from "./login.actions";

export interface State {
    auth: any;
    error: any;
    isLoading: boolean;
}

const initialState: State = {
    auth: null,
    error: null,
    isLoading: false
};

export const loginReducer = createReducer( initialState,
    on( loginBySignature, state => ( { ...state, isLoading: true } ) ),
    on( loginBySignatureSuccess, ( state, { auth }) => ( { ...state, auth, isLoading: false } ) ),
    on( loginBySignatureFailure, ( state, { error }) => ( { ...state, error, isLoading: false } ) )
);
