import { Component, OnInit, OnDestroy, Inject } from '@angular/core';

import { AuthService } from '../application/services/auth.service'

import cssCardGameString from '../application/assets/CardGame.scss'
import cssGameString from './bridge-belote.component.scss'
import templateString from './bridge-belote.component.html'

declare var $: any;

@Component({
    selector: 'app-bridge-belote',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssCardGameString || 'Template Not Loaded !!!',
        cssGameString || 'Template Not Loaded !!!',
    ],
    providers: [AuthService]
})
export class BridgeBeloteComponent implements OnInit, OnDestroy
{
    isLoggedIn: boolean = false;
    
    constructor(
        @Inject(AuthService) private authStore: AuthService
    ) {
        /* */
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
}
