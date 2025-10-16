import { Component, OnDestroy, Inject } from '@angular/core';
import { Subscription } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';
import { AppStateService } from '../../../state/app-state.service';
import BridgeBeloteScoreDto from '_@/GamePlatform/Model/CardGame/bridgeBeloteScoreDto';

import templateString from './bridge-belote-statistics.component.html'
import cssString from './bridge-belote-statistics.component.scss'

declare var $: any;

@Component({
    selector: 'bridge-belote-statistics',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class BridgeBeloteStatisticsComponent implements OnDestroy
{
    scoreSubs: Subscription;
    
    wePoints: number[] = [];
    youPoints: number[] = [];
    
    weTotalPoints: number= 0;
    youTotalPoints: number= 0;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService
    ) {
        this.scoreSubs = this.appStateService.bridgeBeloteScore.observe().subscribe( this.scoreChanged.bind( this ) );
    }
    
    ngOnDestroy()
    {
        this.scoreSubs.unsubscribe();
    }
    
    scoreChanged( dto: BridgeBeloteScoreDto ): void
    {
        this.wePoints.push( dto.SouthNorthPoints );
        this.youPoints.push( dto.EastWestPoints );
        
        this.weTotalPoints = dto.SouthNorthTotalInRoundPoints;
        this.youTotalPoints = dto.EastWestTotalInRoundPoints;
    }
}
