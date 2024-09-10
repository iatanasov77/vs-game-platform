import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { AuthService } from './auth.service';
import ErrorReportDto from '_@/GamePlatform/Model/BoardGame/errorReportDto';

@Injectable({
    providedIn: 'root'
})
export class ErrorReportService
{
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService
    ) { }
    
    saveErrorReport( dto: ErrorReportDto ): void
    {
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        
        this.httpClient.post<ErrorReportDto>( 'errorreport', dto, {headers} ).subscribe();
    }
}
