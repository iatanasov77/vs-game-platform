import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

import { IAuth } from '../interfaces/auth';

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
    
    constructor()
    {
        let auth        = this.getAuth();
        this.loggedIn   = auth && auth.apiToken ? true : false;
        this.loggedIn$  = new BehaviorSubject<boolean>( this.loggedIn );
    }
    
    public isLoggedIn(): Observable<boolean>
    {
        return this.loggedIn$.asObservable();
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
        localStorage.removeItem( this.authKey );
        
        this.loggedIn   = false;
        if ( ! this.loggedIn$ ) {
            this.loggedIn$  = new BehaviorSubject<boolean>( this.loggedIn );
        }
        
        this.loggedIn$.next( this.loggedIn );
    }
}
