import {
    Component,
    Inject,
    Input,
    Output,
    OnInit
} from '@angular/core';

import { TranslateService } from '@ngx-translate/core';
import { GetTrumps, GetAnnounceSymbol } from '@vankosoft/game-platform';
import { CardGameAnnounceSymbolModel } from '@vankosoft/game-platform';
import { ContractBridgeBidDto } from '@vankosoft/game-platform';
import { PlayerPosition } from '@vankosoft/game-platform';
import { PlayerPositions } from '@vankosoft/game-platform';
import { Helper } from '@vankosoft/game-platform';
import { BidTrump } from '@vankosoft/game-platform';
import { AppStateService } from '../../../state/app-state.service';

import cssString from './card-game-bid-history.component.scss';
import templateString from './card-game-bid-history.component.html';

@Component({
    selector: 'card-game-bid-history',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class CardGameBidHistoryComponent implements OnInit
{
    firstInRound: PlayerPosition = PlayerPosition.neither;
    announceSymbols: Array<CardGameAnnounceSymbolModel> = [];
    
    bidHistory: ContractBridgeBidDto[] = [];
    bidHistoryTransformed: any[] = [];
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService,
    ) {
        this.announceSymbols    = GetTrumps();
    }
    
    ngOnInit(): void
    {
        this.firstInRound           = this.appStateService.cardGame.getValue().currentPlayer;
        
        this.bidHistory             = this.appStateService.cardGame.getValue().bidHistory;
        this.bidHistoryTransformed  = Helper.splitAtN( this.bidHistoryForTemplate( this.bidHistory ), 4 );
    }
    
    hystoryTableHeaders(): string[]
    {
        let position: PlayerPosition = this.firstInRound;
        let headers: string[] = [];
        
        headers.push( Helper.cardgamePlayerPosition( position ) );
        let i:number = 0;
        while( true ) {
            position = PlayerPositions.Next( position );
            headers.push( Helper.cardgamePlayerPosition( position ) );
            
            i++;
            if ( i == 3 ) break;
        }
        
        return headers;
    }
    
    bidHistoryForTemplate( bidHistory: ContractBridgeBidDto[] ): string[]
    {
        let bidHistoryForTemplate: string[] = [];
        let outputElement;
        
        for ( let i = 0; i < bidHistory.length; i++ )  {
            if ( bidHistory[i].Trump == BidTrump.Pass ) {
                outputElement = 'Pass';
            } else {
                let announceSymbol = GetAnnounceSymbol( bidHistory[i].Trump );
                outputElement = `${bidHistory[i].Value} ${announceSymbol.htmlEntity}`;
            }
            
            bidHistoryForTemplate.push( outputElement );
        }
        
        return bidHistoryForTemplate;
    }
}