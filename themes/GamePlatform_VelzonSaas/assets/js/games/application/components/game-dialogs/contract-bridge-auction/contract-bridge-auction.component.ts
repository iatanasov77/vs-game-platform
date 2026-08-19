import {
    Component,
    ChangeDetectionStrategy,
    Inject,
    EventEmitter,
    Output
} from '@angular/core';

import { MatButtonToggleModule } from '@angular/material/button-toggle';
import { TranslateService } from '@ngx-translate/core';

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
    
    myPosition: PlayerPosition;
    lastBid: ContractBridgeBidDto | null = null;
    
    bidValueChanged: boolean = false;
    bidValue: number | null = null;
    
    bidTrumpChanged: boolean = false;
    bidTrump: BidTrump | null = null;
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
        @Inject( CardGameService ) private wsService: CardGameService,
    ) {
        this.myPosition = this.appStateService.myPosition.getValue();
    }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    onChangeBidValue(): void
    {
        this.bidValueChanged = true;
    }
    
    setBidValue( group: any ): void
    {
        if ( ! this.bidValueChanged ) {
            group.value = null;
            this.bidValue = null;
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
            this.bidValue = null;
        }
        this.bidTrumpChanged = false;
        // alert( this.bidTrump );
        
        if ( this.bidTrump && ! this.bidValue ) {
            this.bidValue = 1;
        }
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
