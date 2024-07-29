import { Component, OnInit, OnDestroy, Inject, ElementRef, isDevMode } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';

import { AuthService } from '../application/services/auth.service'
import { ApiService } from '../application/services/api.service'
import { IGame } from '../application/interfaces/game';
import { UserLoginComponent } from '../application/components/authentication/user-login/user-login.component';

import cssString from './game-platform.component.scss'
import templateString from './game-platform.component.html'

import { loadGame, loadGameSuccess, loadGameFailure } from '../application/+store/actions';
import { getGame } from '../application/+store/selectors';
declare var $: any;

@Component({
    selector: 'app-layout',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ],
    providers: [AuthService]
})
export class GamePlatformComponent implements OnInit, OnDestroy
{
    apiVerifySiganature?: string;
    isLoggedIn: boolean = false;
    errorFetcingData    = false;
    
    game$               = this.store.select( getGame );
    game: IGame | null  = null;
    
    isFetchingGame$ = merge(
        this.actions$.pipe(
            ofType( loadGame ),
            map( () => true )
        ),
        this.actions$.pipe(
            ofType( loadGameSuccess ),
            map( () => false )
        ),
        this.actions$.pipe(
            ofType( loadGameFailure ),
            map( () => false )
        )
    );
    
    constructor(
        @Inject(ElementRef) private elementRef: ElementRef,
        @Inject(AuthService) private authStore: AuthService,
        @Inject(ApiService) private apiService: ApiService,
        @Inject(NgbModal) private ngbModal: NgbModal,
        
        @Inject(Store) private store: Store,
        @Inject(Actions) private actions$: Actions
    ) {
        this.authStore.isLoggedIn().subscribe( ( isLoggedIn: boolean ) => {
            //alert( isLoggedIn );
            this.isLoggedIn = isLoggedIn;
        });
    }
    
    ngOnInit(): void
    {
        //this.store.dispatch( loadGameBySlug( { slug: 'bridge-belote' } ) );
        
        /*
        this.store.dispatch( loadTablature( { tabId: +params['id'] } ) ); // (+) converts string 'id' to a number
        */
        this.store.dispatch( loadGame( { id: 8 } ) );
            
        this.store.subscribe( ( state: any ) => {
            this.game = state.main.game;
            //console.log( this.game );
        });
    }
    
    ngOnDestroy()
    {

    }
    
    showLoginForm( event: any ): void
    {
        const modalRef = this.ngbModal.open( UserLoginComponent );
        modalRef.componentInstance.closeModalLogin.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
    }
    
    showMyProfile( event: any ): void
    {
        
    }
}
