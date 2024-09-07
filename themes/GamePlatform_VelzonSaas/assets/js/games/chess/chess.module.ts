import { NgModule, InjectionToken } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { APP_BASE_HREF, Location } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { CustomTranslateLoader } from '../application/providers/i18n-provider';

import { HttpClient, HTTP_INTERCEPTORS, provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { BaseUrlInterceptor } from '../application/services/bse-url-interceptor';

import { StoreModule, provideStore, ActionReducerMap } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

import { loginReducer } from '../application/+store/login.reducers';
import { LoginEffects } from '../application/+store/login.effects';

import { gameReducer, GameState } from '../application/+store/game.reducers';
import { CustomSerializer } from '../application/+store/router';
import { GameEffects } from '../application/+store/game.effects';
import { IAppState, getReducers } from '../application/+store/state';

import { ChessComponent } from './chess.component';
import { SharedModule } from '../application/components/shared/shared.module';
import { GameBoardsModule } from '../application/components/game-boards/game-boards.module';
import { NgxChessBoardModule } from 'ngx-chess-board';

export const FEATURE_REDUCER_TOKEN = new InjectionToken<ActionReducerMap<IAppState>>( 'Game Reducers' );

@NgModule({
    declarations: [
        ChessComponent,
    ],
    imports: [
        BrowserModule,
        MatTooltipModule,
        NgbModule,
        //HttpClientModule,
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
        NgxChessBoardModule.forRoot(),
        
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
    bootstrap: [ChessComponent],
    providers: [
        //{ provide: Window, useValue: window },
        { provide: APP_BASE_HREF, useValue: window.location.pathname },
        { provide: FEATURE_REDUCER_TOKEN, useFactory: getReducers },
        
        provideHttpClient( withInterceptorsFromDi() ),
        {provide: HTTP_INTERCEPTORS, useClass: BaseUrlInterceptor, multi: true},
    ]
})
export class ChessModule { }
