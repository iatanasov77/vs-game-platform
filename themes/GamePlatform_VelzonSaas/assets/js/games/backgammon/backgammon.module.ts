import { NgModule, InjectionToken, ErrorHandler } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { APP_BASE_HREF, Location } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { CustomTranslateLoader } from '../application/providers/i18n-provider';

import { HttpClient, HTTP_INTERCEPTORS, provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { BaseUrlInterceptor } from '../application/services/base-url-interceptor';

import { StoreModule, provideStore, ActionReducerMap } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

import { loginReducer } from '../application/+store/login.reducers';
import { LoginEffects } from '../application/+store/login.effects';

import { gameReducer, GameState } from '../application/+store/game.reducers';
import { CustomSerializer } from '../application/+store/router';
import { GameEffects } from '../application/+store/game.effects';
import { IAppState, getReducers } from '../application/+store/state';

import { GlobalErrorService } from '../application/services/global-error-service';
import { BackgammonComponent } from './backgammon.component';
import { SharedModule } from '../application/components/shared/shared.module';
import { GameBoardsModule } from '../application/components/game-boards/game-boards.module';

export const FEATURE_REDUCER_TOKEN = new InjectionToken<ActionReducerMap<IAppState>>( 'Game Reducers' );

@NgModule({
    declarations: [
        BackgammonComponent,
    ],
    imports: [
        BrowserModule,
        BrowserAnimationsModule,
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
        GameBoardsModule,
        
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
    bootstrap: [BackgammonComponent],
    providers: [
        //{ provide: Window, useValue: window },
        { provide: APP_BASE_HREF, useValue: window.location.pathname },
        { provide: FEATURE_REDUCER_TOKEN, useFactory: getReducers },
        
        provideHttpClient( withInterceptorsFromDi() ),
        { provide: HTTP_INTERCEPTORS, useClass: BaseUrlInterceptor, multi: true },
        { provide: ErrorHandler, useClass: GlobalErrorService },
    ]
})
export class BackgammonModule { }
