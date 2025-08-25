import { Injectable, Inject } from '@angular/core';

import { HttpClient, HttpHeaders } from '@angular/common/http'
const { context } = require( '../context' );

import { AuthService } from './auth.service';
import ErrorReportDto from '_@/GamePlatform/Model/Core/errorReportDto';

@Injectable({
    providedIn: 'root'
})
export class ErrorReportService
{
    url: string;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService
    ) {
        this.url    = `${context.apiURL}`;
    }
    
    saveErrorReport( dto: ErrorReportDto ): void
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.url}/errorreport`;
        
        this.httpClient.post<ErrorReportDto>( url, dto, {headers} ).subscribe();
    }
}
