import { Component, Inject, Input, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Store } from '@ngrx/store';

import { playerAnnounce } from '../../../../+store/game.actions';

import { GetAnnounceSymbols } from '../../../../models/announce';
import CardGameAnnounceSymbolModel from '_@/GamePlatform/Model/CardGameAnnounceSymbolModel';
import * as GameEvents from '_@/GamePlatform/Game/GameEvents';

import templateString from './bridge-belote-announce.component.html'
import styleString from './bridge-belote-announce.component.scss'
declare var $: any;

@Component({
    selector: 'bridge-belote-announce',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class BridgeBeloteAnnounceComponent implements OnChanges
{
    @Input() gameAnnounceIcon: any;
    @Input() announceVisible = false;
    @Input() gameContractVisible = false;
    
    announceSymbols: Array<CardGameAnnounceSymbolModel>;
    
    constructor(
        @Inject( Store ) private store: Store
    ) {
        this.announceSymbols = GetAnnounceSymbols();
        this.gameAnnounceIcon   = null;
    }
        
    ngOnChanges( changes: SimpleChanges )
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'gameAnnounceIcon':
                    this.gameAnnounceIcon = changedProp.currentValue;
                    break;
                case 'announceVisible':
                    this.announceVisible = changedProp.currentValue;
                    break;
                case 'gameContractVisible':
                    this.gameContractVisible = changedProp.currentValue;
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