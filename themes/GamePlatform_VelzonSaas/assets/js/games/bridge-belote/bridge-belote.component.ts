import { Component, OnInit, OnDestroy, Inject, ElementRef, isDevMode } from '@angular/core';
import { Store } from '@ngrx/store';

import { loginBySignature } from '../application/+store/login.actions';
import { selectAuth, selectError, selectIsLoading } from '../application/+store/login.selectors';

import { AuthService } from '../application/services/auth.service'

import cssGameString from './bridge-belote.component.scss'
import templateString from './bridge-belote.component.html'

declare var $: any;

@Component({
    selector: 'app-bridge-belote',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ],
    providers: [AuthService]
})
export class BridgeBeloteComponent implements OnInit, OnDestroy
{
    apiVerifySiganature?: string;
    
    isLoggedIn: boolean         = false;
    developementClass: string   = '';
    
    auth        = null;
    error       = '';
    isLoading   = false;
  
    constructor(
        @Inject( ElementRef ) private elementRef: ElementRef,
        @Inject( AuthService ) private authStore: AuthService,
        @Inject( Store ) private store: Store
    ) {
        this.store.select( selectAuth ).subscribe( state => ( this.auth = state ) );
        this.store.select( selectError ).subscribe( state => ( this.error = state ) );
        this.store.select( selectIsLoading ).subscribe( state => ( this.isLoading = state ) );
    
        if( isDevMode() ) {
            this.developementClass  = 'developement';
        }
        
        this.apiVerifySiganature = this.elementRef.nativeElement.getAttribute( 'apiVerifySiganature' );
        
        this.authStore.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            //alert( isLoggedIn );
            this.isLoggedIn = isLoggedIn;
        });
        
        if ( ! this.isLoggedIn && this.apiVerifySiganature?.length ) {
             //this.apiService.loginBySignature( this.apiVerifySiganature );
             
             this.store.dispatch( loginBySignature( { apiVerifySiganature: this.apiVerifySiganature } ) );
        }
        
        //this.debugApplication();
    }
    
    ngOnInit(): void
    {
        
    }
    
    ngOnDestroy()
    {

    }
    
    debugApplication()
    {
        if ( this.apiVerifySiganature?.length ) {
            alert( this.apiVerifySiganature );
        } else {
            alert( 'Missing Login By Signature URL !!!' );
        }
        
        alert( this.isLoggedIn );
    }  
}
