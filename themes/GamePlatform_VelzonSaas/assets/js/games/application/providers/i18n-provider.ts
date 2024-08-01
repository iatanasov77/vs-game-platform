import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { TranslateLoader } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';

const { context } = require( '../context' );
declare var $: any;

@Injectable({
    providedIn: 'root'
})
export class CustomTranslateLoader implements TranslateLoader
{
    backendURL: string;
    
    constructor( @Inject( HttpClient ) private http: HttpClient )
    {
        this.backendURL = context.backendURL;
    }
    
    public getTranslation( lang: String ): Observable<any>
    {
        var url = this.backendURL + '/get-translations/' + $( '#GameContainer' ).attr( 'data-locale' );
        
        return this.http.get( url ).pipe(
            map( ( res: any ) => {
                return res;
            })
        );
    }
}