import { Injectable, Inject } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient, HttpHeaders } from '@angular/common/http'
import { BehaviorSubject, Observable, tap, map, take, finalize } from 'rxjs';
import { AppConstants } from "../constants";

import { IAuth } from '../interfaces/auth';
import { ISignedUrlResponse } from '../interfaces/signed-url-response';
import { IToggleSoundMuteResponse } from '../interfaces/toggle-sound-mute-response';

import { SoundService } from './sound.service';
import { StatusMessageService } from './status-message.service';
import { StorageService, LOCAL_STORAGE } from 'ngx-webstorage-service';
import { AppStateService } from '../state/app-state.service';
import { Busy } from '../state/busy';
import { Keys } from '../utils/keys';
import UserDto from '_@/GamePlatform/Model/BoardGame/userDto';

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
    authKey = "auth";
    
    loggedIn: boolean;
    loggedIn$: BehaviorSubject<boolean>;
    
    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( LOCAL_STORAGE ) private storage: StorageService,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
        @Inject( TranslateService ) private trans: TranslateService,
        @Inject( SoundService ) private sound: SoundService,
    ) {
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
    
    loginBySignature( apiVerifySiganature: string ): Observable<IAuth>
    {
        var url = 'login-by-signature/' + apiVerifySiganature;
        
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
                    //this.statusMessageService.setWaitingForConnect();
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
        // Remove Backgamon User from Local Storage
        this.signOut();
        this.statusMessageService.setNotLoggedIn();
        
        localStorage.removeItem( this.authKey );
        
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
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + idToken );
        
        // Gets or creates the user in backgammon database.
        this.httpClient.post<UserDto>( 'account/signin', userDto, {headers} ).pipe(
            map( ( data: any ) => { return data; } )
        ).subscribe( ( userDto: UserDto ) => {
            this.storage.set( Keys.loginKey, userDto );
            this.appState.user.setValue( userDto );
            this.appState.hideBusy();
        });
    }
    
    signOut(): void
    {
        this.appState.user.clearValue();
        this.storage.remove( Keys.loginKey );
    }
    
    repair(): void
    {
        const user = this.storage.get( Keys.loginKey ) as UserDto;
        this.appState.user.setValue( user );
    }
    
    toggleIntro(): void
    {
        const headers = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.getApiToken() );
        
        let mute = false;
        this.httpClient.get<IToggleSoundMuteResponse>( 'account/toggleIntro', {headers} ).pipe(
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
