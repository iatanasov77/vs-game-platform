import { Component, OnInit, OnDestroy, Inject, ElementRef } from '@angular/core';

import { AuthService } from '../application/services/auth.service'

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
    
    isLoggedIn: boolean = false;
    
    constructor(
        @Inject(ElementRef) private elementRef: ElementRef,
        @Inject(AuthService) private authStore: AuthService
    ) {
        this.urlLoginBySignature = this.elementRef.nativeElement.getAttribute( 'urlLoginBySignature' );
        //this.debugApplication();
        
        this.authStore.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            this.isLoggedIn = isLoggedIn;
        });
    
    }
    
    ngOnInit(): void
    {
        
    }
    
    ngOnDestroy()
    {

    }
    
    debugApplication()
    {
        alert( this.urlLoginBySignature );
        //alert( this.isLoggedIn );
    }  
}
