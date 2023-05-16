import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http'
import { Restangular } from 'ngx-restangular';

import { AuthService } from './auth.service';

const {context} = require( '../context' );
const backendURL = context.backendURL;

/**
 * Restangular Manual: https://github.com/2muchcoffeecom/ngx-restangular
 */
@Injectable({
    providedIn: 'root'
})
export class ApiService
{
    constructor(
        @Inject(HttpClient) private httpClient: HttpClient,
        @Inject(Restangular) private restangular: Restangular,
        @Inject(AuthService) private authStore: AuthService
    ) { }
    
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
}
