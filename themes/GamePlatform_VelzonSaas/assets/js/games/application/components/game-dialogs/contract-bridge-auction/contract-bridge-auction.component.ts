import {
    Component,
    ChangeDetectionStrategy,
    Inject,
    EventEmitter,
    Output
} from '@angular/core';
import { ButtonComponent } from '../../shared/button/button.component';
import { TranslateService } from '@ngx-translate/core';

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
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService
    ) {}
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    makeBid(): void
    {
        this.dismissModal();
    }
}
