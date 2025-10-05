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
    @Input() currentPlayer: PlayerPosition | undefined;
    
    @Output() onPlayerMakeBid = new EventEmitter<BidDto>();
    
    announceSymbols: Array<CardGameAnnounceSymbolModel>;
    myPosition: PlayerPosition;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
    ) {
        this.announceSymbols = GetAnnounceSymbols();
        this.gameAnnounceIcon   = null;
        
        this.myPosition = this.appStateService.myPosition.getValue();
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
                case 'currentPlayer':
                    this.currentPlayer = changedProp.currentValue;
                    break;
            }
        }
    }
    
    getClass( bid: BidType ): string
    {
        if ( ! ( bid in this.validBids ) ) {
            return 'announce-disabled';
        }
        
        return '';
    }
    
    makeBid( bidType: BidType )
    {
        let bid: BidDto = {
            Player: this.myPosition,
            Type: bidType,
            NextBids: []
        };
        
        this.onPlayerMakeBid.emit( bid );
    }
}