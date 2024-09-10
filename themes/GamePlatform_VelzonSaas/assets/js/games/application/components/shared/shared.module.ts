import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';

import { LoaderComponent } from './loader/loader.component';
import { MessagesComponent } from './messages/messages.component';
import { BusyComponent } from './busy/busy.component';
import { ErrorHandlerComponent } from './error-handler/error-handler.component';
import { GameRequirementsDialogComponent } from './dialogs/game-requirements-dialog/game-requirements-dialog.component';

@NgModule({
    declarations: [
        LoaderComponent,
        MessagesComponent,
        BusyComponent,
        ErrorHandlerComponent,
        GameRequirementsDialogComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
    ],
    exports: [
        LoaderComponent,
        MessagesComponent,
        BusyComponent,
        ErrorHandlerComponent,
        GameRequirementsDialogComponent
    ]
})
export class SharedModule { }
