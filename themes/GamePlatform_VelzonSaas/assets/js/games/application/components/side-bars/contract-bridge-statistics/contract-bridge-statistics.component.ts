import { Component, OnDestroy, Inject } from '@angular/core';
import { Subscription } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';
import { AppStateService } from '../../../state/app-state.service';
import { ContractBridgeScoreDto } from '@vankosoft/game-platform';
import { CardGameDto } from '@vankosoft/game-platform';
import { BidTrump } from '@vankosoft/game-platform';
import { GameState } from '@vankosoft/game-platform';

import templateString from './contract-bridge-statistics.component.html'
import cssString from './contract-bridge-statistics.component.scss'

declare var $: any;

@Component({
    selector: 'contract-bridge-statistics',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class ContractBridgeStatisticsComponent implements OnDestroy
{
    scoreSubs: Subscription;
    gameSubs: Subscription;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService
    ) {
        this.scoreSubs = this.appStateService.contractBridgeScore.observe().subscribe( this.scoreChanged.bind( this ) );
        this.gameSubs = this.appStateService.cardGame.observe().subscribe( this.gameChanged.bind( this ) );
    }
    
    ngOnDestroy()
    {
        this.scoreSubs.unsubscribe();
    }
    
    gameChanged( dto: CardGameDto ): void
    {
        if ( dto && dto.playState === GameState.ended ) {
            
        }
    }
    
    scoreChanged( dto: ContractBridgeScoreDto ): void
    {
        //console.log( 'ContractBridgeScoreDto', dto );
        if ( dto.contract == BidTrump.Pass ) {
            return;
        }
    }
}
