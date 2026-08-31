import {
    Component,
    ChangeDetectionStrategy,
    Inject,
    EventEmitter,
    Output,
    OnInit
} from '@angular/core';

import { TranslateService } from '@ngx-translate/core';

import { GetTrumps, GetAnnounceSymbol } from '@vankosoft/game-platform';
import { CardGameAnnounceSymbolModel } from '@vankosoft/game-platform';

import { BidTrump } from '@vankosoft/game-platform';
import { ContractBridgeBidDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';
import { Helper } from '@vankosoft/game-platform';

import { AppStateService } from '../../../state/app-state.service';
import { CardGameService } from '../../../services/websocket/card-game.service';

import cssString from './contract-bridge-auction.component.scss';
import templateString from './contract-bridge-auction.component.html';

@Component({
    selector: 'app-contract-bridge-auction',
    changeDetection: ChangeDetectionStrategy.OnPush,
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class ContractBridgeAuctionComponent implements OnInit
{
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    @Output() passEntry: EventEmitter<any> = new EventEmitter();
    
    announceSymbols: Array<CardGameAnnounceSymbolModel> = [];
    
    validBids: ContractBridgeBidDto[] = [];
    bidHistory: ContractBridgeBidDto[] = [];
    contract: ContractBridgeBidDto | null = null;
    lastBid?: ContractBridgeBidDto;
    lastBidString?: string;
    myPosition: PlayerPosition;
    
    bidValueChanged: boolean = false;
    bidValue: number = 1;
    
    bidTrumpChanged: boolean = false;
    bidTrump: BidTrump | null = null;
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( CardGameService ) private wsService: CardGameService,
    ) {
        this.myPosition         = this.appStateService.myPosition.getValue();
        this.announceSymbols    = GetTrumps();
        
        this.validBids          = this.appStateService.cardGame.getValue().validBids;
        this.bidHistory         = this.appStateService.cardGame.getValue().bidHistory;
    }
    
    ngOnInit(): void
    {
        this.lastBid        = this.bidHistory.filter( ( bh ) => bh.Trump != BidTrump.Pass ).pop();
        this.lastBidString  = this.bidTrumpString();
    }
    
    dismissModal(): void
    {
        this.makeBid();
    }
    
    bidValueIsDisabled( value: number ): boolean
    {
        if (
            this.bidTrump &&
            this.contract &&
            this.bidTrump == this.contract.Trump &&
            value <= this.contract.Value
        ) {
            return true;
        }
        
        return false;
    }
    
    bidTrumpIsDisabled( trump: BidTrump ): boolean
    {
        return ! this.validBids.some( b => b.Trump === trump );
    }
    
    onChangeBidValue(): void
    {
        this.bidValueChanged = true;
    }
    
    setBidValue( group: any ): void
    {
        if ( ! this.bidValueChanged ) {
            group.value = 1;
            this.bidValue = 1;
            this.bidTrump = null;
        }
        this.bidValueChanged = false;
        // alert( this.bidValue );
    }
    
    onChangeBidTrump(): void
    {
        this.bidTrumpChanged = true;
    }
    
    setBidTrump( group: any ): void
    {
        if ( ! this.bidTrumpChanged ) {
            group.value = null;
            this.bidTrump = null;
            this.bidValue = 1;
        }
        this.bidTrumpChanged = false;
        // alert( this.bidTrump );
    }
    
    makeBid(): void
    {
        if ( this.myPosition === PlayerPosition.neither ) {
            alert( 'Make a Bid from Wrong Player Position !!!' );
            return;
        }
        
        let bidValue = this.bidValue ? this.bidValue : 0;
        let bidTrump = this.bidTrump ? this.bidTrump : BidTrump.Pass;
        
        let bid: ContractBridgeBidDto = {
            Player: this.myPosition,
            Value: bidValue,
            Trump: bidTrump,
            LastBid: false,
            NextBids: []
        };
        this.doBid( bid );
        
        this.passEntry.emit( bid );
        this.closeModal.emit();
    }
    
    doBid( bid: ContractBridgeBidDto ): void
    {
        this.wsService.doBid( bid );
        this.wsService.sendBid( bid );
    }
    
    playerPositionString( position: PlayerPosition ): string
    {
        return Helper.cardgamePlayerPosition( position );
    }
    
    bidTrumpString()
    {
        if ( ! this.lastBid ) {
            return;
        }
        let announceSymbol = GetAnnounceSymbol( Number( this.lastBid.Trump ) );
        
        return `${this.lastBid.Value} ${announceSymbol.htmlEntity}`;
    }
}