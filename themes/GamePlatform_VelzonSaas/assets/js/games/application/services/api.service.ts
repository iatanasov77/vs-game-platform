import { Injectable, Inject } from '@angular/core';
import { Observable } from 'rxjs';

import { Restangular } from 'ngx-restangular';

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
        @Inject( Restangular ) private restangular: Restangular
    ) {
        this.backendURL = context.backendURL;
    }
    
    loadTranslations( locale: string )
    {
        return this.restangular.one( 'get-translations/' + locale ).get();
    }
}
