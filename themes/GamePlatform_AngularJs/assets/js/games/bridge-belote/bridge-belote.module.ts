import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { APP_BASE_HREF, Location } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';

import { RestangularModule } from 'ngx-restangular';
import { RestangularConfigFactory } from '../application/restangular.config';

import { StoreModule } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

import { reducers } from '../application/+store';
import { CustomSerializer } from '../application/+store/router';
import { Effects } from '../application/+store/effects';

import { AppRoutingModule } from './app-routing.module';

import { BridgeBeloteComponent } from './bridge-belote.component';
import { GameTableModule } from './components/game-table.module';


@NgModule({
    declarations: [
        BridgeBeloteComponent,
    ],
    imports: [
        BrowserModule,
        MatTooltipModule,
        
        AppRoutingModule,
        RestangularModule.forRoot( RestangularConfigFactory ),
        StoreModule.forRoot( reducers ),
        //EffectsModule.forRoot( [Effects] ),
        //StoreRouterConnectingModule.forRoot( { serializer: CustomSerializer } ),
        
        GameTableModule,
    ],
    bootstrap: [BridgeBeloteComponent],
    providers: [
        //{ provide: Window, useValue: window },
        { provide: APP_BASE_HREF, useValue: window.location.pathname }
    ]
})
export class BridgeBeloteModule { }
