import { Injectable, Inject } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import { HttpClient, HttpHeaders } from '@angular/common/http'
const { context } = require( '../context' );

import { BehaviorSubject, Observable, tap, map, take, finalize } from 'rxjs';
import { AppConstants } from "../constants";

import { IAuth } from '../interfaces/auth';
import { ISignedUrlResponse } from '../interfaces/signed-url-response';
import { IToggleSoundMuteResponse } from '../interfaces/toggle-sound-mute-response';

import { LocalStorageService } from './local-storage.service';
import { SoundService } from './sound.service';
import { StatusMessageService } from './status-message.service';
import { AppStateService } from '../state/app-state.service';
import { Busy } from '../state/busy';
import { Keys } from '../utils/keys';
import UserDto from '_@/GamePlatform/Model/Core/userDto';

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
    url: string;
    authKey = "auth";
    
    loggedIn: boolean;
    loggedIn$: BehaviorSubject<boolean>;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
        @Inject( TranslateService ) private trans: TranslateService,
        @Inject( SoundService ) private sound: SoundService,
        @Inject( LocalStorageService ) private localStorageService: LocalStorageService,
    ) {
        this.url        = `${context.apiURL}`;
        
        let auth        = this.getAuth();
        this.loggedIn   = auth && auth.apiToken ? true : false;
        this.loggedIn$  = new BehaviorSubject<boolean>( this.loggedIn );
    }
    
    public isLoggedIn(): Observable<boolean>
    {
        return this.loggedIn$.asObservable();
    }
    
    login( username: string, password: string ): Observable<IAuth>
    {
        var url = `${this.url}/login_check`;
        let postData = { username : username, password :password };
        
        return this.httpClient.post<IAuth>( url, postData ).pipe(
            tap( ( response: any ) => {
                if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.payload ) {
                    let auth: IAuth = {
                        id: response.payload.userId,
                        
                        email: response.payload.email,
                        username: response.payload.username,
                        
                        fullName: response.payload.userFullName,
                        
                        apiToken: response.payload.token,
                        tokenCreated: response.payload.tokenCreated,
                        tokenExpired: response.payload.tokenExpired,
                        
                        apiRefreshToken: response.refresh_token,
                    };
                    
                    this.createAuth( auth );
                    
                    // Add Backgamon User in Local Storage
                    this.signIn( this.createUserDto( auth ), auth.apiToken );
                }
            })
        );
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
        var url = `${this.url}/login-by-signature/${apiVerifySiganature}`;
        
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
                    
                    // Add Backgamon User in Local Storage
                    this.signIn( this.createUserDto( auth ), auth.apiToken );
                }
            })
        );
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
        this.localStorageService.setItem( this.authKey, auth );
        
        this.loggedIn   = auth && auth.apiToken ? true : false;
        this.loggedIn$.next( this.loggedIn );
    }
    
    public getAuth(): IAuth | null
    {
        let auth    = this.localStorageService.getItem<IAuth>( this.authKey );
        
        if ( auth && this.checkTokenExpired( auth ) ) {
            auth    = null;
        }
        
        return auth;
    }
    
    public removeAuth()
    {
        // Remove Backgamon User from Local Storage
        this.signOut();
        this.statusMessageService.setNotLoggedIn();
        
        this.localStorageService.removeItem( this.authKey );
        
        this.loggedIn   = false;
        if ( ! this.loggedIn$ ) {
            this.loggedIn$  = new BehaviorSubject<boolean>( this.loggedIn );
        }
        
        this.loggedIn$.next( this.loggedIn );
    }
    
    public createUserDto( auth: IAuth ): UserDto
    {
        let userDto = {
            id: String( auth.id ),
            name: auth.username,
            email: auth.email,
            socialProviderId: String( auth.id ),
            socialProvider: '',
            photoUrl: '',
            createdNew: false
        } as UserDto;
        
        return userDto;
    }
    
    signIn( userDto: UserDto, idToken: string ): void
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + idToken );
        var url         = `${this.url}/account/signin`;
        
        // Gets or creates the user in backgammon database.
        this.httpClient.post<UserDto>( url, userDto, {headers} ).pipe(
            map( ( data: any ) => { return data; } )
        ).subscribe( ( userDto: UserDto ) => {
            this.trans.use( userDto?.preferredLanguage ?? 'en' );
            this.localStorageService.setItem( Keys.loginKey, userDto );
            this.appState.user.setValue( userDto );
            if ( userDto ) this.appState.changeTheme( userDto?.theme );
            this.appState.hideBusy();
        });
    }
    
    signOut(): void
    {
        this.appState.user.clearValue();
        this.localStorageService.removeItem( Keys.loginKey );
    }
    
    // If the user account is stored in local storage, it will be restored without contacting social provider
    repair(): void
    {
        const user = this.localStorageService.getItem( Keys.loginKey ) as UserDto;
        this.appState.user.setValue( user );
        this.trans.use( user?.preferredLanguage ?? 'en' );
        if ( user ) {
            this.appState.changeTheme( user.theme );
            // this.synchUser();
        }
        
        //alert( 'Auth Repair' );
        //console.log( 'User', user );
    }
    
    toggleIntro(): void
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.getApiToken() );
        var url         = `${this.url}/account/toggleIntro`;
        
        let mute = false;
        this.httpClient.get<IToggleSoundMuteResponse>( url, {headers} ).pipe(
            map( ( response ) => {
                if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.mute ) {
                    mute = response.mute;
                }
                const user = this.appState.user.getValue();
                this.appState.user.setValue( { ...user, muteIntro: mute } );
                if ( mute ) {
                    this.sound.fadeIntro();
                } else {
                    this.sound.unMuteIntro();
                }
            }),
            take( 1 )
        ).subscribe();
    }
}
