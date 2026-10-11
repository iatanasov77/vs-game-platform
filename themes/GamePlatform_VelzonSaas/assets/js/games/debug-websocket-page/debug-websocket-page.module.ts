import { NgModule, ErrorHandler } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { APP_BASE_HREF } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { HttpClientModule, HttpClient } from '@angular/common/http';

import { StoreModule } from '@ngrx/store';
import { loginReducer } from '../application/+store/login.reducers';

import { GlobalErrorService } from '../application/services/global-error-service';
import { DebugWebsocketPageComponent } from './debug-websocket-page.component';
import { DebugWebsocketModule } from '../application/components/debug-websocket/debug-websocket.module';

export function HttpLoaderFactory( http: HttpClient ) {
    return new TranslateHttpLoader( http, '/build/gameplatform-velzonsaas-theme/i18n/', '.json' );
}

@NgModule({
    declarations: [
        DebugWebsocketPageComponent,
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
        
        DebugWebsocketModule,
        
        StoreModule.forRoot([
            loginReducer,
        ]),
    ],
    bootstrap: [DebugWebsocketPageComponent],
    providers: [
        { provide: APP_BASE_HREF, useValue: window.location.pathname },
        { provide: ErrorHandler, useClass: GlobalErrorService }
    ]
})
export class DebugWebsocketPageModule { }
