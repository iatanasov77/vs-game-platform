import { Component, Inject, Input, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Store } from '@ngrx/store';

import { playerAnnounce } from '../../../../+store/game.actions';

import { GetAnnounceSymbols } from '../../../../models/announce';
import CardGameAnnounceSymbolModel from '_@/GamePlatform/Model/CardGameAnnounceSymbolModel';
import * as GameEvents from '_@/GamePlatform/Game/GameEvents';

import templateString from './card-game-announce.component.html'
declare var $: any;

@Component({
    selector: 'card-game-announce',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: []
})
export class CardGameAnnounceComponent implements OnInit, OnChanges
{
    @Input() gameAnnounceIcon: any;
    
    announceSymbols: Array<CardGameAnnounceSymbolModel>;
    
    constructor(
        @Inject( Store ) private store: Store
    ) {
        this.announceSymbols = GetAnnounceSymbols();
        this.gameAnnounceIcon   = null;
    }
    
    ngOnInit(): void
    {
        $( '#AnnounceContainer' ).hide();
        $( '#GameAnnounce' ).hide();
    }
        
    ngOnChanges( changes: SimpleChanges )
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'gameAnnounceIcon':
                    this.gameAnnounceIcon = changedProp.currentValue;
                    break;
            }
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
    
    onPlayerAnnounceNew( announceId: any, event: any )
    {
        this.store.dispatch( playerAnnounce() );
        this.store.subscribe( ( state: any ) => {
            //this.showSpinner    = state.main.latestTablatures == null;
        });
    }
}