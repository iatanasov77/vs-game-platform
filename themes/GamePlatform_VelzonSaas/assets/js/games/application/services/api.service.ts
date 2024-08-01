import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http'
import { Restangular } from 'ngx-restangular';

import { AuthService } from './auth.service';
import { IAuth } from '../interfaces/auth';
import { ISignedUrlResponse } from '../interfaces/signed-url-response';

import { AppConstants } from "../constants";
const { context } = require( '../context' );

/**
 * Restangular Manual: https://github.com/2muchcoffeecom/ngx-restangular
 */
@Injectable({
    providedIn: 'root'
})
export class ApiService
{
    backendURL: string;
    
    constructor(
        @Inject(HttpClient) private httpClient: HttpClient,
        @Inject(Restangular) private restangular: Restangular,
        @Inject(AuthService) private authStore: AuthService
    ) {
        this.backendURL = context.backendURL;
    }
    
    loadTranslations( locale: string )
    {
        return this.restangular.one( 'get-translations/' + locale ).get();
    }
    
    /*
     * Centralize Get Api Token To Can Check if Expired and someday to use a reffreah token
     * ===========================================================================================
     *      https://github.com/markitosgv/JWTRefreshTokenBundle
     *      https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html#about-token-expiration
     */
     getApiToken(): string
     {
        let auth        = this.authStore.getAuth();
        
        return auth ? auth.apiToken : '';
     }
     
    login( credentials: any )
    {
        return this.restangular.all( "login_check" ).post( credentials );
    }
    
    logout()
    {
        // Need to Logout From Api Server
    
        this.authStore.removeAuth();
    }
    
    register( formData: any )
    {
        return this.restangular.all( "users/register" ).post( formData );
    }
    
    loginBySignature( apiVerifySiganature: string )
    {
        var url = this.backendURL + '/login-by-signature/' + apiVerifySiganature;
        
        this.httpClient.get<ISignedUrlResponse>( url ).subscribe( ( response: ISignedUrlResponse ) => {            
            if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
                let auth: IAuth = {
                    id: response.data.user.id,
                    
                    email: response.data.user.email,
                    username: response.data.user.username,
                    
                    fullName: response.data.user.firstName + ' ' + response.data.user.lastName,
                    
                    apiToken: response.data.tokenString,
                    tokenCreated: response.data.token.iat,
                    tokenExpired: response.data.token.exp,
                };
                
                this.authStore.createAuth( auth );
            }
        });
    }
    
    loadGame( id: number )
    {
        return this.restangular.one( 'games/' + id ).get();
    }
    
    loadGameBySlug( slug: string )
    {
        return this.restangular.one( 'games/' + slug )
                    .customGET( 
                        undefined,
                        undefined,
                        {"Authorization": 'Bearer ' + this.getApiToken()}
                    );
    }
}
