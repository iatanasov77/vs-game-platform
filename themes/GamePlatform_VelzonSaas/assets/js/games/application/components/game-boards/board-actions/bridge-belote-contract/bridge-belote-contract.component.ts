import { Component, Inject, Input, OnChanges, SimpleChanges, EventEmitter, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import { GetAnnounceSymbols } from '../../../../models/announce';
import CardGameAnnounceSymbolModel from '_@/GamePlatform/Model/CardGameAnnounceSymbolModel';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import BidType from '_@/GamePlatform/Model/CardGame/bidType';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';

import { AppStateService } from '../../../../state/app-state.service';

import templateString from './bridge-belote-contract.component.html'
import styleString from './bridge-belote-contract.component.scss'
declare var $: any;

@Component({
    selector: 'bridge-belote-contract',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class BridgeBeloteContractComponent implements OnChanges
{
    @Input() gameAnnounceIcon: any;
    @Input() gameContractVisible = false;
    @Input() validBids: BidDto[] = [];
    
    @Output() onPlayerMakeBid = new EventEmitter<BidDto>();
    
    announceSymbols: Array<CardGameAnnounceSymbolModel>;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
    ) {
        this.announceSymbols = GetAnnounceSymbols();
        this.gameAnnounceIcon   = null;
    }
        
    ngOnChanges( changes: SimpleChanges )
    {
        //console.log( 'BridgeBeloteAnnounceComponent Changes', changes );
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'gameAnnounceIcon':
                    this.gameAnnounceIcon = changedProp.currentValue;
                    break;
                case 'gameContractVisible':
                    this.gameContractVisible = changedProp.currentValue;
                    break;
                case 'validBids':
                    this.validBids = changedProp.currentValue;
                    //alert( 'Valid Bids: ' + this.validBids.length );
                    break;
            }
        }
    }
    
    getClass( bid: BidType ): string
    {
        if ( ! ( bid in this.validBids ) ) {
            return 'disabled';
        }
        
        return '';
    }
    
    makeBid( bidType: BidType )
    {
        const myPosition: PlayerPosition = this.appStateService.myPosition.getValue()
        
        let bid: BidDto = {
            Player: myPosition,
            Type: bidType,
            NextBids: []
        };
        
        this.onPlayerMakeBid.emit( bid );
    }
}