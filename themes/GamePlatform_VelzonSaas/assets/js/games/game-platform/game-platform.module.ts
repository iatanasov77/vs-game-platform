import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { APP_BASE_HREF, Location } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { RestangularModule } from 'ngx-restangular';
import { RestangularConfigFactory } from '../application/restangular.config';

import { StoreModule } from '@ngrx/store';
import { StoreRouterConnectingModule } from '@ngrx/router-store';
import { EffectsModule } from '@ngrx/effects';

import { reducers } from '../application/+store';
import { CustomSerializer } from '../application/+store/router';
import { Effects } from '../application/+store/effects';

import { AppRoutingModule } from './app-routing.module';

import { GamePlatformComponent } from './game-platform.component';

import { AuthenticationModule } from '../application/components/authentication/authentication.module';
import { SharedModule } from '../application/components/shared/shared.module';

@NgModule({
    declarations: [
        GamePlatformComponent,
    ],
    imports: [
        BrowserModule,
        MatTooltipModule,
        NgbModule,
        
        AppRoutingModule,
        RestangularModule.forRoot( RestangularConfigFactory ),
        StoreModule.forRoot( reducers ),
        EffectsModule.forRoot( [Effects] ),
        //StoreRouterConnectingModule.forRoot( { serializer: CustomSerializer } ),
        
        AuthenticationModule,
        SharedModule,
    ],
    bootstrap: [GamePlatformComponent],
    providers: [
        //{ provide: Window, useValue: window },
        { provide: APP_BASE_HREF, useValue: window.location.pathname }
    ]
})
export class GamePlatformModule { }
