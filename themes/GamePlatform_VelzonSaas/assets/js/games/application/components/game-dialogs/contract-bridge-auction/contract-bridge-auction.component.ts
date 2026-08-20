import {
    Component,
    ChangeDetectionStrategy,
    Inject,
    EventEmitter,
    Output
} from '@angular/core';

import { MatButtonToggleModule } from '@angular/material/button-toggle';
import { TranslateService } from '@ngx-translate/core';

import { GetTrumps, GetAnnounceSymbol } from '@vankosoft/game-platform';
import { CardGameAnnounceSymbolModel } from '@vankosoft/game-platform';

import { BidTrump } from '@vankosoft/game-platform';
import { ContractBridgeBidDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';

import { AppStateService } from '../../../state/app-state.service';
import { CardGameService } from '../../../services/websocket/card-game.service';

import { ButtonComponent } from '../../shared/button/button.component';

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
export class ContractBridgeAuctionComponent
{
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    announceSymbols: Array<CardGameAnnounceSymbolModel> = [];
    
    validBids: ContractBridgeBidDto[] = [];
    contract: ContractBridgeBidDto | null = null;
    
    myPosition: PlayerPosition;
    lastBid: ContractBridgeBidDto | null = null;
    
    bidValueChanged: boolean = false;
    bidValue: number = 1;
    
    bidTrumpChanged: boolean = false;
    bidTrump: BidTrump | null = null;
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( CardGameService ) private wsService: CardGameService,
    ) {
        this.myPosition = this.appStateService.myPosition.getValue();
        this.getAnnounceSymbols();
        
        this.validBids = this.appStateService.cardGame.getValue().validBids;
    }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    getAnnounceSymbols(): void
    {
        this.announceSymbols = GetTrumps();
        return;
        
        this.announceSymbols = [];
        var symbol;
        for ( var i = 0; i < this.validBids.length; i++ ) {
            symbol = GetAnnounceSymbol( this.validBids[i].Trump );
            if ( symbol ) {
                this.announceSymbols.push( symbol );
            }
        }
    }
    
    bidValueIsDisabled( value: number ): boolean
    {
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
        let bidValue = this.bidValue ? this.bidValue : 0;
        let bidTrump = this.bidTrump ? this.bidTrump : BidTrump.Pass;
        
        let bid: ContractBridgeBidDto = {
            Player: this.myPosition,
            Value: bidValue,
            Trump: bidTrump,
            NextBids: []
        };
        //alert( JSON.stringify( bid ) );
        
        this.doBid( bid );
        this.dismissModal();
    }
    
    doBid( bid: ContractBridgeBidDto ): void
    {
        this.wsService.doBid( bid );
        this.wsService.sendBid( bid );
        /*  
        let playerBids = this.appStateService.playerBids.getValue();
        alert( playerBids.length );
        */
    }
}
