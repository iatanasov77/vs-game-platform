import { NgModule, InjectionToken, ErrorHandler } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { APP_BASE_HREF } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { HttpClientModule, HttpClient } from '@angular/common/http';

import { StoreModule, ActionReducerMap } from '@ngrx/store';
import { EffectsModule } from '@ngrx/effects';

import { loginReducer } from '../application/+store/login.reducers';
import { LoginEffects } from '../application/+store/login.effects';

import { GameEffects } from '../application/+store/game.effects';
import { IAppState, getReducers } from '../application/+store/state';

import { GlobalErrorService } from '../application/services/global-error-service';
import { BridgeBeloteComponent } from './bridge-belote.component';
import { SharedModule } from '../application/components/shared/shared.module';
import { GameBoardsModule } from '../application/components/game-boards/game-boards.module';
import { SideBarsModule } from '../application/components/side-bars/side-bars.module';

export const FEATURE_REDUCER_TOKEN = new InjectionToken<ActionReducerMap<IAppState>>( 'Game Reducers' );

export function HttpLoaderFactory( http: HttpClient ) {
    return new TranslateHttpLoader( http, '/build/gameplatform-velzonsaas-theme/i18n/', '.json' );
}

@NgModule({
    declarations: [
        BridgeBeloteComponent,
    ],
    imports: [
        BrowserModule,
        BrowserAnimationsModule,
        MatTooltipModule,
        NgbModule,
        
        HttpClientModule,
        TranslateModule.forRoot({
            defaultLanguage: 'en',
            loader: {
                provide: TranslateLoader,
                useFactory: HttpLoaderFactory,
                deps: [HttpClient]
            }
        }),
        
        SharedModule,
        GameBoardsModule,
        SideBarsModule,
        
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
        { provide: APP_BASE_HREF, useValue: window.location.pathname },
        { provide: FEATURE_REDUCER_TOKEN, useFactory: getReducers },
        { provide: ErrorHandler, useClass: GlobalErrorService }
    ]
})
export class BridgeBeloteModule { }
