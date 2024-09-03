import { Injectable, Inject } from '@angular/core';
import { BehaviorSubject, Observable, tap } from 'rxjs';

import { HttpClient } from '@angular/common/http'
import { Restangular } from 'ngx-restangular';

const { context } = require( '../context' );
import { AppConstants } from "../constants";
import { IAuth } from '../interfaces/auth';
import { ISignedUrlResponse } from '../interfaces/signed-url-response';

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
    backendURL: string;
    
    authKey = "auth";
    
    loggedIn: boolean;
    loggedIn$: BehaviorSubject<boolean>;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( Restangular ) private restangular: Restangular
    ) {
        this.backendURL = context.backendURL;
        
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
    
    login( credentials: any )
    {
        return this.restangular.all( "login_check" ).post( credentials );
    }
    
    loginBySignature( apiVerifySiganature: string ): Observable<IAuth>
    {
        var url = this.backendURL + '/login-by-signature/' + apiVerifySiganature;
        
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
    
    logout()
    {
        // Need to Logout From Api Server
    
        this.removeAuth();
    }
    
    register( formData: any )
    {
        return this.restangular.all( "users/register" ).post( formData );
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
}
