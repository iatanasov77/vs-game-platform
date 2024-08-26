import { Component, Inject, OnInit, OnDestroy, Input, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Store } from '@ngrx/store';

import * as GameEvents from '_@/GamePlatform/Game/GameEvents';

import templateString from './card-game-board.component.html'
import styleString from './card-game-board.component.scss'
declare var $: any;

@Component({
    selector: 'card-game-board',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class CardGameBoardComponent implements OnInit, OnDestroy, OnChanges
{
    @Input() isLoggedIn: boolean        = false;
    @Input() hasPlayer: boolean         = false;
    @Input() developementClass: string  = '';
    
    @Input() game?: any;
    @Input() gameProvider?: any;

    gameStarted: boolean = false;
    gameAnnounceIcon: any;
    announceSymbols: any;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
        @Inject( Store ) private store: Store
    ) {
        this.gameAnnounceIcon   = null;
    }
    
    ngOnInit(): void
    {
        this.announceSymbols    = this.gameProvider?.getAnnounceSymbols();
        this.game?.initBoard();
        this.listenForGameEvents();
        
        this.store.subscribe( ( state: any ) => {
            if ( state.app.main.cardGame ) {
                this.gameStarted    = true;
            }
        });
    }
    
    ngOnDestroy(): void
    {

    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'developementClass':
                    this.developementClass = changedProp.currentValue;
                    break;
                case 'isLoggedIn':
                    this.isLoggedIn = changedProp.currentValue;
                    break;
                case 'hasPlayer':
                    this.hasPlayer = changedProp.currentValue;
                    break;
            }
        }
    }
    
    listenForGameEvents(): void
    {
        $( "#card-table" ).get( 0 ).addEventListener( GameEvents.GAME_START_EVENT_NAME, ( event: any ) => {
            const { announceId }    = event.detail;
            
            this.gameAnnounceIcon   = this.gameProvider.getAnnounceSymbol( announceId )?.value;
            
            $( '#AnnounceContainer' ).hide();
            $( '#GameAnnounce' ).show();
        });
    }
}
