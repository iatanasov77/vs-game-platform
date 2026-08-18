import {
    Component,
    ChangeDetectionStrategy,
    Inject,
    EventEmitter,
    Output
} from '@angular/core';

import { MatButtonToggleModule } from '@angular/material/button-toggle';
import { TranslateService } from '@ngx-translate/core';

import { BidDto } from '@vankosoft/game-platform';
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
    
    lastBid: BidDto | null = null;
    bidValue: number | null = null;
    bidTrump: string | null = null;
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService
    ) {}
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    setBidValue( value: number ): void
    {
        // alert( value );
        this.bidValue = value;
    }
    
    setBidTrump( trump: string ): void
    {
        // alert( trump );
        this.bidTrump = trump;
    }
    
    makeBid(): void
    {
        this.dismissModal();
    }
}
