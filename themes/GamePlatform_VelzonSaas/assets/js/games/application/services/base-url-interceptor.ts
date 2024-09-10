import { Injectable } from '@angular/core';
import {
    HttpRequest,
    HttpHandler,
    HttpEvent,
    HttpInterceptor,
} from '@angular/common/http';
import { Observable } from 'rxjs';
const { context } = require( '../context' );

@Injectable()
export class BaseUrlInterceptor implements HttpInterceptor
{
    baseUrl: string = context.backendURL;
    
    intercept( request: HttpRequest<unknown>, next: HttpHandler ): Observable<HttpEvent<unknown>>
    {
        const apiReq = request.clone( { url: `${this.baseUrl}/${request.url}` } );
        return next.handle( apiReq );
    }
}