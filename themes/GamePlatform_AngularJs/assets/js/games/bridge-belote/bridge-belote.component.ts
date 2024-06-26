import { Component, OnInit, OnDestroy, Inject, ElementRef, isDevMode } from '@angular/core';

import { AuthService } from '../application/services/auth.service'
import { ApiService } from '../application/services/api.service'

import cssCardGameString from '../application/assets/CardGame.scss'
import cssGameString from './bridge-belote.component.scss'
import templateString from './bridge-belote.component.html'

declare var $: any;

@Component({
    selector: 'app-bridge-belote',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssCardGameString || 'CardGame CSS Not Loaded !!!',
        cssGameString || 'Game CSS Not Loaded !!!',
    ],
    providers: [AuthService]
})
export class BridgeBeloteComponent implements OnInit, OnDestroy
{
    urlLoginBySignature?: string;
    
    isLoggedIn: boolean         = false;
    developementClass: string   = '';
    
    constructor(
        @Inject(ElementRef) private elementRef: ElementRef,
        @Inject(AuthService) private authStore: AuthService,
        @Inject(ApiService) private apiService: ApiService
    ) {
        if( isDevMode() ) {
            this.developementClass  = 'developement';
        }
        
        this.urlLoginBySignature = this.elementRef.nativeElement.getAttribute( 'urlLoginBySignature' );
        
        this.authStore.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            //alert( isLoggedIn );
            this.isLoggedIn = isLoggedIn;
        });
    
        if ( ! this.isLoggedIn && this.urlLoginBySignature?.length ) {
            this.apiService.loginBySignedUrl( this.urlLoginBySignature )
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
        if ( this.urlLoginBySignature?.length ) {
            alert( this.urlLoginBySignature );
        } else {
            alert( 'Missing Login By Signature URL !!!' );
        }
        
        alert( this.isLoggedIn );
    }  
}
