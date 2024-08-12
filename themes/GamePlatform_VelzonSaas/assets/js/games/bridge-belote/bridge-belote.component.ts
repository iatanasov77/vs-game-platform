import { Component, OnInit, Inject, ElementRef, isDevMode } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { pluck, map } from 'rxjs/operators';
import { Store, provideStore } from '@ngrx/store';
import { provideEffects } from '@ngrx/effects';
import Swal from 'sweetalert2'

import { loginBySignature, loginBySignatureSuccess } from '../application/+store/login.actions';
import { selectAuth, selectError, selectIsLoading } from '../application/+store/login.selectors';
import { AuthState } from '../application/+store/login.reducers';
import { AuthService } from '../application/services/auth.service'
import { IAuth } from '../application/interfaces/auth';

import cssGameString from './bridge-belote.component.scss'
import templateString from './bridge-belote.component.html'

declare var $: any;

@Component({
    selector: 'app-bridge-belote',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class BridgeBeloteComponent implements OnInit
{
    auth$: Observable<AuthState>;
    
    isLoggedIn: boolean         = false;
    developementClass: string   = '';
    apiVerifySiganature?: string;

    constructor(
        @Inject( ElementRef ) private elementRef: ElementRef,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( Store ) private store: Store
    ) {
        if( isDevMode() ) {
            this.developementClass  = 'developement';
        }
        
        this.apiVerifySiganature = this.elementRef.nativeElement.getAttribute( 'apiVerifySiganature' );
        this.auth$     = this.store.select( selectAuth );

        if ( this.apiVerifySiganature?.length ) {
            this.store.dispatch( loginBySignature( { apiVerifySiganature: this.apiVerifySiganature } ) );
        }
    }
    
    ngOnInit()
    {
        this.authService.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            //console.log( isLoggedIn );
            //console.log( this.getAuthFromService() );
            this.isLoggedIn = isLoggedIn;
        });
    }
    
    public getAuthFromService()
    {
        return this.authService.getAuth();
    }
}
