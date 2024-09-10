import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { APP_BASE_HREF, Location } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';

import { StoreModule } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

//import { gameReducers } from '../application/+store/game.reducers';
//import { CustomSerializer } from '../application/+store/router';
//import { Effects } from '../+store/game.effects';

import { AppRoutingModule } from './app-routing.module';

import { ContractBridgeComponent } from './contract-bridge.component';

@NgModule({
    declarations: [
        ContractBridgeComponent,
        
    ],
    imports: [
        BrowserModule,
        MatTooltipModule,
        
        AppRoutingModule,
        //RestangularModule.forRoot( RestangularConfigFactory ),
        //StoreModule.forRoot( gameReducers ),
        //EffectsModule.forRoot( [Effects] ),
        //StoreRouterConnectingModule.forRoot( { serializer: CustomSerializer } ),
    ],
    bootstrap: [ContractBridgeComponent],
    providers: [
        //{ provide: Window, useValue: window },
        { provide: APP_BASE_HREF, useValue: window.location.pathname }
    ]
})
export class ContractBridgeModule { }
