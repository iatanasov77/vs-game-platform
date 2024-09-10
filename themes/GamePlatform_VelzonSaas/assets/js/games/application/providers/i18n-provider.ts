import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { TranslateLoader } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';

declare var $: any;

@Injectable({
    providedIn: 'root'
})
export class CustomTranslateLoader implements TranslateLoader
{
    constructor( @Inject( HttpClient ) private http: HttpClient ) { }
    
    public getTranslation( lang: String ): Observable<any>
    {
        var url = 'get-translations/' + $( '#GameContainer' ).attr( 'data-locale' );
        
        return this.http.get( url ).pipe(
            map( ( res: any ) => {
                return res;
            })
        );
    }
}