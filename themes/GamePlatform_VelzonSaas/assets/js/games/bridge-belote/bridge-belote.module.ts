import { NgModule, InjectionToken } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { APP_BASE_HREF, Location } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { CustomTranslateLoader } from '../application/providers/i18n-provider';

import { RestangularModule } from 'ngx-restangular';
import { RestangularConfigFactory } from '../application/restangular.config';

import { StoreModule, provideStore, ActionReducerMap } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

import { loginReducer } from '../application/+store/login.reducers';
import { LoginEffects } from '../application/+store/login.effects';

import { gameReducer, GameState } from '../application/+store/game.reducers';
import { CustomSerializer } from '../application/+store/router';
import { GameEffects } from '../application/+store/game.effects';
import { IAppState, getReducers } from '../application/+store/state';

import { AppRoutingModule } from './app-routing.module';

import { BridgeBeloteComponent } from './bridge-belote.component';
import { GameBoardsModule } from '../application/components/game-boards/game-boards.module';
import { SharedModule } from '../application/components/shared/shared.module';

export const FEATURE_REDUCER_TOKEN = new InjectionToken<ActionReducerMap<IAppState>>( 'Game Reducers' );

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
        GameBoardsModule,
        SharedModule,
        
        //AppRoutingModule,
        //StoreRouterConnectingModule.forRoot( { serializer: CustomSerializer } ),
        RestangularModule.forRoot( RestangularConfigFactory ),
        
        StoreModule.forRoot([
            loginReducer,
        ]),
        EffectsModule.forRoot([
            LoginEffects,
        ]),
        
        StoreModule.forFeature( 'app', FEATURE_REDUCER_TOKEN ),
        EffectsModule.forFeature([
            GameEffects,
        ]),
    ],
    bootstrap: [BridgeBeloteComponent],
    providers: [
        //{ provide: Window, useValue: window },
        { provide: APP_BASE_HREF, useValue: window.location.pathname },
        { provide: FEATURE_REDUCER_TOKEN, useFactory: getReducers },
    ]
})
export class BridgeBeloteModule { }
