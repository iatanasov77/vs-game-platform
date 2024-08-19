import { Injectable, Inject } from "@angular/core";

import { createEffect, Actions, ofType } from '@ngrx/effects';
import { catchError, map, switchMap } from "rxjs/operators";

import {
    loginBySignature,
    loginBySignatureFailure,
    loginBySignatureSuccess
} from "./login.actions";

import { AuthService } from "../services/auth.service";
import { IAuth } from '../interfaces/auth';

/**
 * Effects are an RxJS powered side effect model for Store. Effects use streams to provide new sources of actions to reduce state based on external interactions such 
 * as network requests, web socket messages and time-based events.
 * 
 * In a service-based Angular application, components are responsible for interacting with external resources directly through services. Instead, effects provide a way 
 * to interact with those services and isolate them from the components. Effects are where you handle tasks such as fetching data, long-running tasks that produce 
 * multiple events, and other external interactions where your components don't need explicit knowledge of these interactions.
 */

@Injectable()
export class LoginEffects
{
    constructor(
        @Inject( Actions ) private actions$: Actions,
        @Inject( AuthService ) private authService: AuthService
    ) { }
    
    loginBySignature$ = createEffect( (): any =>
        this.actions$.pipe(
            ofType( loginBySignature ),
            switchMap( ( { apiVerifySiganature } ) =>
                this.authService.loginBySignature( apiVerifySiganature ).pipe(
                    map( auth => loginBySignatureSuccess( { auth } ) ),
                    catchError( error => [loginBySignatureFailure( { error } )] )
                )
            )
        )
    );
}

