import { Component, Inject, Input, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Store } from '@ngrx/store';

import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';
import { playerAnnounce } from '../../../../../+store/game.actions';

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
    @Input() announceSymbols: any;
    
    constructor(
        @Inject( Store ) private store: Store
    ) {
        this.gameAnnounceIcon   = null;
        this.announceSymbols    = [];
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
                case 'announceSymbols':
                    this.announceSymbols = changedProp.currentValue;
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