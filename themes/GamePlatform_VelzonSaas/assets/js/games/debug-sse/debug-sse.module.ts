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
import { DebugSseComponent } from './debug-sse.component';

export function HttpLoaderFactory( http: HttpClient ) {
    return new TranslateHttpLoader( http, '/build/gameplatform-velzonsaas-theme/i18n/', '.json' );
}

@NgModule({
    declarations: [
        DebugSseComponent,
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
        
        StoreModule.forRoot([
            loginReducer,
        ]),
    ],
    bootstrap: [DebugSseComponent],
    providers: [
        { provide: APP_BASE_HREF, useValue: window.location.pathname },
        { provide: ErrorHandler, useClass: GlobalErrorService }
    ]
})
export class DebugSseModule { }
