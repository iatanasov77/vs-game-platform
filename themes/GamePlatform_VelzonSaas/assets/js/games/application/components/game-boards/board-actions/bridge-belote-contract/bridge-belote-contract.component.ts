import { Component, Inject, Input, OnChanges, SimpleChanges, EventEmitter, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import { GetAnnounceSymbol } from '@vankosoft/game-platform';
import { CardGameAnnounceSymbolModel } from '@vankosoft/game-platform';

import { PlayerPosition } from '@vankosoft/game-platform';
import { ContractBridgeBidDto } from '@vankosoft/game-platform';
import { BidTrump } from '@vankosoft/game-platform';
import { BidDto } from '@vankosoft/game-platform';

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
    
    announceSymbols: Array<CardGameAnnounceSymbolModel> = [];
    myPosition: PlayerPosition;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
    ) {
        this.myPosition = this.appStateService.myPosition.getValue();
    }
    
    ngOnChanges( changes: SimpleChanges )
    {
        //console.log( 'BridgeBeloteAnnounceComponent Changes', changes );
        //alert( 'BridgeBeloteContractComponent Changes !!!' );
        
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'gameContractVisible':
                    //alert( 'Game ContractVisible: ' + changedProp.currentValue );
                    this.gameContractVisible = changedProp.currentValue;
                    break;
                case 'gameBiddingVisible':
                    /*  
                    alert(`
                        Game BiddingVisible: ${changedProp.currentValue}\n
                        Current Player: ${this.currentPlayer}\n
                        My Position: ${this.myPosition}
                    `);
                    */
                    this.gameBiddingVisible = changedProp.currentValue;
                    break;
                case 'validBids':
                    this.validBids = changedProp.currentValue;
                    this.getAnnounceSymbols();
                    //console.log( 'Valid Bids', this.validBids );
                    //alert( 'Valid Bids: ' + this.validBids.length );
                    break;
                case 'contract':
                    //alert( 'Game Contract: ' + changedProp.currentValue );
                    this.contract = changedProp.currentValue;
                    //alert( 'Valid Bids: ' + this.validBids.length );
                    //console.log( 'Cuurent Contract', this.contract );
                    break;
                case 'currentPlayer':
                    this.currentPlayer = changedProp.currentValue;
                    
                    /** Needed Because in Constructor Get Wrong My Position */
                    this.myPosition = this.appStateService.myPosition.getValue();
                    
                    break;
            }
        }
    }
    
    getAnnounceSymbols(): void
    {
        this.announceSymbols = [];
        var symbol;
        for ( var i = 0; i < this.validBids.length; i++ ) {
            symbol = GetAnnounceSymbol( this.validBids[i].Trump );
            if ( symbol ) {
                this.announceSymbols.push( symbol );
            }
        }
    }
    
    getContractPlayer(): string
    {
        if ( ! this.contract ) {
            return '';
        }
        
        return PlayerPosition[this.contract.Player];
    }
    
    getContractKontraPlayer(): string
    {
        //console.log( 'Kontra Player', this?.contract?.KontraPlayer );
        if ( ! this.contract ) {
            return '';
        }
        
        if ( this.contract.KontraPlayer == null ) {
            return '';
        }
        
        return PlayerPosition[this.contract.KontraPlayer];
    }
    
    getContractReKontraPlayer(): string
    {
        if ( ! this.contract ) {
            return '';
        }
        
        if ( this.contract.ReKontraPlayer == null ) {
            return '';
        }
        
        return PlayerPosition[this.contract.ReKontraPlayer];
    }
    
    getContractIcon(): string
    {
        if ( ! this.contract ) {
            return '';
        }
        // alert( JSON.stringify( this.contract ) );
        
        let value: any = '';
        if ( 'Value' in this.contract ) { //  && this.contract.Value
            //alert( 'Type of Contract Value: ' + typeof value );
            if ( this.contract.Value != 0 ) {
                value = this.contract.Value;
            }
        }
        
        //console.log( 'Current Contract', this.contract );
        switch ( this.contract.Trump ) {
            case BidTrump.Clubs:
                return `( ${value}<i class="fi fi-sr-club"></i> )`;
                break;
            case BidTrump.Diamonds:
                return `( ${value}<i class="fi fi-sr-card-diamond"></i> )`;
                break;
            case BidTrump.Hearts:
                return `( ${value}<i class="fi fi-sr-heart"></i> )`;
                break;
            case BidTrump.Spades:
                return `( ${value}<i class="fi fi-sr-spade"></i> )`;
                break;
            case BidTrump.NoTrumps:
                return `( ${value}a )`;
                break;
            case BidTrump.AllTrumps:
                return '( j )';
                break;
            default:
                return '';
        }
    }
    
    makeBid( bidTrump: BidTrump ): void
    {
        if ( this.myPosition === PlayerPosition.neither ) {
            // alert( `Make a Bid from Wrong Player Position. Currunt Player: ${this.currentPlayer}` );
            return;
        }
        
        let bid: BidDto = {
            Player: this.myPosition,
            Trump: bidTrump,
            LastBid: false,
            NextBids: []
        };
        
        this.onPlayerMakeBid.emit( bid );
    }
}