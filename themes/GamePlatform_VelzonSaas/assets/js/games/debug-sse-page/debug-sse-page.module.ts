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
import { DebugSsePageComponent } from './debug-sse-page.component';
import { DebugSseModule } from '../application/components/debug-sse/debug-sse.module';

export function HttpLoaderFactory( http: HttpClient ) {
    return new TranslateHttpLoader( http, '/build/gameplatform-velzonsaas-theme/i18n/', '.json' );
}

@NgModule({
    declarations: [
        DebugSsePageComponent,
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
        
        DebugSseModule,
        
        StoreModule.forRoot([
            loginReducer,
        ]),
    ],
    bootstrap: [DebugSsePageComponent],
    providers: [
        { provide: APP_BASE_HREF, useValue: window.location.pathname },
        { provide: ErrorHandler, useClass: GlobalErrorService }
    ]
})
export class DebugSsePageModule { }
