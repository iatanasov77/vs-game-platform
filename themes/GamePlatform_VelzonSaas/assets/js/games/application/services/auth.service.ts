import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http'
import { BehaviorSubject, Observable, tap, map } from 'rxjs';
import { AppConstants } from "../constants";

import { IAuth } from '../interfaces/auth';
import { ISignedUrlResponse } from '../interfaces/signed-url-response';

import { StorageService, LOCAL_STORAGE } from 'ngx-webstorage-service';
import { AppState } from '../state/app-state';
import { Busy } from '../state/busy';
import { Keys } from '../utils/keys';
import UserDto from '_@/GamePlatform/Model/BoardGame/userDto';

/**
 * Manual: https://blog.jscrambler.com/working-with-angular-local-storage/
 *===========================================================================
 * In Manual has How to decrypt data if its contains sensitive information
 */
@Injectable({
    providedIn: 'root'
})
export class AuthService
{
    authKey = "auth";
    
    loggedIn: boolean;
    loggedIn$: BehaviorSubject<boolean>;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( LOCAL_STORAGE ) private storage: StorageService,
    ) {
        let auth        = this.getAuth();
        this.loggedIn   = auth && auth.apiToken ? true : false;
        this.loggedIn$  = new BehaviorSubject<boolean>( this.loggedIn );
    }
    
    public isLoggedIn(): Observable<boolean>
    {
        return this.loggedIn$.asObservable();
    }
    
    /*
     * Centralize Get Api Token To Can Check if Expired and someday to use a reffreah token
     * ===========================================================================================
     *      https://github.com/markitosgv/JWTRefreshTokenBundle
     *      https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html#about-token-expiration
     */
    getApiToken(): string
    {
        let auth        = this.getAuth();
        
        return auth ? auth.apiToken : '';
    }
    
    loginBySignature( apiVerifySiganature: string ): Observable<IAuth>
    {
        var url = 'login-by-signature/' + apiVerifySiganature;
        
        return this.httpClient.get<ISignedUrlResponse>( url ).pipe(
                    tap( ( response: any ) => {
                        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
                            let auth: IAuth = {
                                id: response.data.user.id,
                                
                                email: response.data.user.email,
                                username: response.data.user.username,
                                
                                fullName: response.data.user.firstName + ' ' + response.data.user.lastName,
                                
                                apiToken: response.data.tokenString,
                                tokenCreated: response.data.token.iat,
                                tokenExpired: response.data.token.exp,
                                
                                apiRefreshToken: response.data.refreshToken,
                            };
                            
                            this.createAuth( auth );
                        }
                    }));
    }
    
    public checkTokenExpired( auth: IAuth ): boolean
    {
        //alert( "Token Expired: " + auth.tokenExpired * 1000 + "\nCurrrent: " + Date.now() );
        if ( ( auth.tokenExpired * 1000 ) < Date.now() ) {
            this.removeAuth();
            
            return true;
        }
        
        return false;
    }
    
    public createAuth( auth: IAuth )
    {
        localStorage.setItem( this.authKey, JSON.stringify( auth ) );
        
        this.loggedIn   = auth && auth.apiToken ? true : false;
        this.loggedIn$.next( this.loggedIn );
    }
    
    public getAuth(): IAuth | null | undefined
    {
        let authData    = localStorage.getItem( this.authKey );
        
        let auth        = authData ? JSON.parse( authData ) : null;
        if ( auth && this.checkTokenExpired( auth ) ) {
            auth    = null;
        }
        
        return auth;
    }
    
    public removeAuth()
    {
        localStorage.removeItem( this.authKey );
        
        this.loggedIn   = false;
        if ( ! this.loggedIn$ ) {
            this.loggedIn$  = new BehaviorSubject<boolean>( this.loggedIn );
        }
        
        this.loggedIn$.next( this.loggedIn );
    }
    
    
    
    
    
    
    
    
    
    signIn( userDto: UserDto, idToken: string ): void
    {
        const options = {
            headers: { Authorization: idToken }
        };
        // Gets or creates the user in backgammon database.
        this.httpClient.post<UserDto>( 'signin', userDto, options ).pipe(
            map( ( data: any ) => { return data; } )
        ).subscribe( ( userDto: UserDto ) => {
            this.storage.set( Keys.loginKey, userDto );
            AppState.Singleton.user.setValue( userDto );
            Busy.hide();
        });
    }
    
    signOut(): void
    {
        AppState.Singleton.user.clearValue();
        this.storage.remove( Keys.loginKey );
    }
    
    repair(): void
    {
        const user = this.storage.get( Keys.loginKey ) as UserDto;
        AppState.Singleton.user.setValue( user );
    }
}
