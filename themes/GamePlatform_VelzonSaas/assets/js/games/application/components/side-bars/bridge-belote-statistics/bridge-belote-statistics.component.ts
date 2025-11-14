import { Component, OnDestroy, Inject } from '@angular/core';
import { Subscription } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';
import { AppStateService } from '../../../state/app-state.service';
import BridgeBeloteScoreDto from '_@/GamePlatform/Model/CardGame/bridgeBeloteScoreDto';
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import BidType from '_@/GamePlatform/Model/CardGame/bidType';
import GameState from '_@/GamePlatform/Model/Core/gameState';

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
    gameSubs: Subscription;
    
    wePoints: string[] = [];
    youPoints: string[] = [];
    
    weTotalPoints: number= 0;
    youTotalPoints: number= 0;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( AppStateService ) private appStateService: AppStateService
    ) {
        this.scoreSubs = this.appStateService.bridgeBeloteScore.observe().subscribe( this.scoreChanged.bind( this ) );
        this.gameSubs = this.appStateService.cardGame.observe().subscribe( this.gameChanged.bind( this ) );
    }
    
    ngOnDestroy()
    {
        this.scoreSubs.unsubscribe();
    }
    
    gameChanged( dto: CardGameDto ): void
    {
        if ( dto && dto.playState === GameState.ended ) {
            this.wePoints = [];
            this.youPoints = [];
        }
    }
    
    scoreChanged( dto: BridgeBeloteScoreDto ): void
    {
        //console.log( 'BridgeBeloteScoreDto', dto );
        if ( dto.contract == BidType.Pass ) {
            return;
        }
        
        if ( dto.SouthNorthPoints ) {
            if ( this.wePoints.length ) {
                let previousPoints = this.wePoints[this.wePoints.length - 1];
                let pointsSum = parseInt( previousPoints ) + dto.SouthNorthPoints;
                this.wePoints[this.wePoints.length - 1] = `${previousPoints} + ${dto.SouthNorthPoints}`;
                this.wePoints.push( '' + pointsSum );
            } else {
                this.wePoints.push( '' + dto.SouthNorthPoints );
            }
            
            this.weTotalPoints = dto.SouthNorthTotalInRoundPoints;
            //console.log( 'SouthNorthPoints', this.wePoints );
        }
        
        if ( dto.EastWestPoints ) {
            if ( this.youPoints.length ) {
                let previousPoints = this.youPoints[this.youPoints.length - 1];
                let pointsSum = parseInt( previousPoints ) + dto.EastWestPoints;
                this.youPoints[this.youPoints.length - 1] = `${previousPoints} + ${dto.EastWestPoints}`;
                this.youPoints.push( '' + pointsSum );
            } else {
                this.youPoints.push( '' + dto.EastWestPoints );
            }
            
            this.youTotalPoints = dto.EastWestTotalInRoundPoints;
            //console.log( 'EastWestPoints', this.youPoints );
        }
    }
}
