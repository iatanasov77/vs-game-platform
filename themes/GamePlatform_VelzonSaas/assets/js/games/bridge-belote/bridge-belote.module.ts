import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { APP_BASE_HREF, Location } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { CustomTranslateLoader } from '../application/providers/i18n-provider';

import { RestangularModule } from 'ngx-restangular';
import { RestangularConfigFactory } from '../application/restangular.config';

import { StoreModule, provideStore } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

import { loginReducer } from '../application/+store/login.reducers';
import { LoginEffects } from '../application/+store/login.effects';

import { gameReducers } from '../application/+store/game.reducers';
import { CustomSerializer } from '../application/+store/router';
import { GameEffects } from '../application/+store/game.effects';

import { AppRoutingModule } from './app-routing.module';

import { BridgeBeloteComponent } from './bridge-belote.component';
import { SharedModule } from '../application/components/shared/shared.module';

@NgModule({
    declarations: [
        BridgeBeloteComponent,
    ],
    imports: [
        BrowserModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forRoot({
            defaultLanguage: 'en',
            loader: {
                provide: TranslateLoader,
                useClass: CustomTranslateLoader,
                deps: [HttpClient]
            }
        }),
        SharedModule,
        
        //AppRoutingModule,
        //StoreRouterConnectingModule.forRoot( { serializer: CustomSerializer } ),
        RestangularModule.forRoot( RestangularConfigFactory ),
        
        StoreModule.forRoot([
            loginReducer
            //gameReducers,
        ]),
        EffectsModule.forRoot([
            LoginEffects,
            //GameEffects
        ])
    ],
    bootstrap: [BridgeBeloteComponent],
    providers: [
        //{ provide: Window, useValue: window },
        { provide: APP_BASE_HREF, useValue: window.location.pathname }
    ]
})
export class BridgeBeloteModule { }
