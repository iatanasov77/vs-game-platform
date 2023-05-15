import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { CommonModule, APP_BASE_HREF } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';

import { RestangularModule } from 'ngx-restangular';
import { RestangularConfigFactory } from '../restangular.config';

import { StoreModule } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

import { reducers } from '../+store';
import { CustomSerializer } from '../+store/router';
//import { Effects } from '../+store/effects';

import { AppRoutingModule } from './app-routing.module';

import { BridgeBeloteComponent } from './bridge-belote.component';
import { GameStatisticsComponent } from './components/game-statistics/game-statistics.component';
import { GameBoardComponent } from './components/game-board/game-board.component';
import { PlayerAnnounceComponent } from './components/player-announce/player-announce.component';



@NgModule({
    declarations: [
        BridgeBeloteComponent,
        GameStatisticsComponent,
        GameBoardComponent,
        PlayerAnnounceComponent,
    ],
    imports: [
        BrowserModule,
        
        CommonModule,
        MatTooltipModule,
        
        AppRoutingModule,
        RestangularModule.forRoot( RestangularConfigFactory ),
        StoreRouterConnectingModule.forRoot( { serializer: CustomSerializer } ),
        StoreModule.forRoot( reducers ),
        //EffectsModule.forRoot( [Effects] ),
    ],
    bootstrap: [BridgeBeloteComponent],
    providers: [{provide: APP_BASE_HREF, useValue: '/game/bridge-belote'}]
})
export class BridgeBeloteModule { }
