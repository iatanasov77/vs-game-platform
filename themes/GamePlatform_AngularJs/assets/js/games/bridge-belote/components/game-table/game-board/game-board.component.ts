import { Component, OnInit, OnDestroy, Inject, Input } from '@angular/core';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';
import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';

import { BridgeBeloteProvider } from '../../../../application/services/providers/bridge-belote-provider';
import templateString from './game-board.component.html'

import { UserLoginComponent } from '../../../../application/components/authentication/user-login/user-login.component';

declare var $: any;

@Component({
    selector: 'game-board',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: []
})
export class GameBoardComponent implements OnInit, OnDestroy
{
    @Input() isLoggedIn: boolean = false;

    providerBridgeBelote: any;
    game: any;
    announceSymbols: any;
    gameAnnounceIcon: any;
    
    constructor(
        //private providerBridgeBelote: BridgeBeloteProvider,
        @Inject(NgbModal) private ngbModal: NgbModal
    ) {
        // DI Not Worked
        this.providerBridgeBelote   = new BridgeBeloteProvider();
        
        this.game                   = new BeloteCardGame( '#card-table', '/build/game-platform-angularjs' );
        this.gameAnnounceIcon       = null;
        this.announceSymbols        = this.providerBridgeBelote.getAnnounceSymbols();
    }
    
    ngOnInit(): void
    {
        this.game.initBoard();
        this.listenForGameEvents();
        
        $( '#AnnounceContainer' ).hide();
        $( '#GameAnnounce' ).hide();
    }
    
    ngOnDestroy(): void
    {

    }
    
    listenForGameEvents()
    {
        $( "#card-table" ).get( 0 ).addEventListener( GameEvents.GAME_START_EVENT_NAME, ( event: any ) => {
            const { announceId }    = event.detail;
            
            this.gameAnnounceIcon   = this.providerBridgeBelote.getAnnounceSymbol( announceId )?.value;
            
            $( '#AnnounceContainer' ).hide();
            $( '#GameAnnounce' ).show();
        });
    }
    
    onStartGame( event: any )
    {
        //event.preventDefault();
        
        if ( this.isLoggedIn ) {
            this.game.startGame();
            $( '#btnStartGame' ).hide();
        } else {
            const modalRef = this.ngbModal.open( UserLoginComponent );
            modalRef.componentInstance.closeModalLogin.subscribe( () => {
                // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
                modalRef.dismiss();
            });
        }
    }
    
    onPlayerAnnounce( announceId: any, event: any )
    {
        event.preventDefault();
        
        $( "#BottomPlayer" ).get( 0 ).dispatchEvent(
            new CustomEvent( GameEvents.PLAYER_ANNOUNCE_EVENT_NAME, {
                detail: {
                    announceId: announceId
                },
            })
        );
    }
}
