import { Component, Inject, Input, OnChanges, SimpleChanges, EventEmitter, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import { GetAnnounceSymbol } from '../../../../models/announce';
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
    @Input() gameBiddingVisible = false;
    @Input() gameContractVisible = false;
    @Input() validBids: BidDto[] = [];
    @Input() currentPlayer: PlayerPosition | undefined;
    @Input() contract: BidDto | undefined;
    
    @Output() onPlayerMakeBid = new EventEmitter<BidDto>();
    
    announceSymbols: Array<CardGameAnnounceSymbolModel>;
    myPosition: PlayerPosition;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
    ) {
        this.announceSymbols = [];
        this.myPosition = this.appStateService.myPosition.getValue();
    }
        
    ngOnChanges( changes: SimpleChanges )
    {
        //console.log( 'BridgeBeloteAnnounceComponent Changes', changes );
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'gameContractVisible':
                    this.gameContractVisible = changedProp.currentValue;
                    break;
                case 'gameBiddingVisible':
                    this.gameBiddingVisible = changedProp.currentValue;
                    break;
                case 'validBids':
                    this.validBids = changedProp.currentValue;
                    this.getAnnounceSymbols();
                    console.log( 'Valid Bids', this.validBids );
                    //alert( 'Valid Bids: ' + this.validBids.length );
                    break;
                case 'contract':
                    this.contract = changedProp.currentValue;
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
    
    getAnnounceSymbols(): void
    {
        this.announceSymbols = [];
        var symbol;
        for ( var i = 0; i < this.validBids.length; i++ ) {
            symbol = GetAnnounceSymbol( this.validBids[i].Type );
            if ( symbol ) {
                this.announceSymbols.push( symbol );
            }
        }
    }
    
    getContractIcon(): string
    {
        if ( ! this.contract ) {
            return '';
        }
        
        //console.log( 'Current Contract', this.contract );
        switch ( this.contract.Type ) {
            case BidType.Clubs:
                return '<i class="fi fi-sr-club"></i>';
                break;
            case BidType.Diamonds:
                return '<i class="fi fi-sr-card-diamond"></i>';
                break;
            case BidType.Hearts:
                return '<i class="fi fi-sr-heart"></i>';
                break;
            case BidType.Spades:
                return '<i class="fi fi-sr-spade"></i>';
                break;
            case BidType.NoTrumps:
                return 'a';
                break;
            case BidType.AllTrumps:
                return 'j';
                break;
            default:
                return '';
        }
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