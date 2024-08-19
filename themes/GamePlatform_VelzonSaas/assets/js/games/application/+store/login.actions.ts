import { createAction, props } from "@ngrx/store";
import { IAuth } from '../interfaces/auth';

const actionTypes = {
    loginBySignature:           'LOGIN_BY_SIGNATURE',
    loginBySignatureSuccess:    'LOGIN_BY_SIGNATURE_SUCCESS',
    loginBySignatureFailure:    'LOGIN_BY_SIGNATURE_FAILURE',
};

export const loginBySignature           = createAction(
    actionTypes.loginBySignature,
    props<{ apiVerifySiganature: any }>()
);

export const loginBySignatureSuccess    = createAction(
    actionTypes.loginBySignatureSuccess,
    props<{ auth: IAuth }>()
);

export const loginBySignatureFailure    = createAction(
    actionTypes.loginBySignatureFailure,
    props<{ error: string }>()
);
