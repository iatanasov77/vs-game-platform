import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateModule } from '@ngx-translate/core';
import { ReactiveFormsModule } from '@angular/forms';

import { LoaderComponent } from './loader/loader.component';
import { MessagesComponent } from './messages/messages.component';
import { BusyComponent } from './busy/busy.component';
import { ErrorHandlerComponent } from './error-handler/error-handler.component';
import { ButtonComponent } from './button/button.component';
import { InputCopyComponent } from './input-copy/input-copy.component';

@NgModule({
    declarations: [
        LoaderComponent,
        MessagesComponent,
        BusyComponent,
        ErrorHandlerComponent,
        ButtonComponent,
        InputCopyComponent
    ],
    imports: [
        CommonModule,
        MatTooltipModule,
        NgbModule,
        TranslateModule.forChild(),
        ReactiveFormsModule,
    ],
    exports: [
        LoaderComponent,
        MessagesComponent,
        BusyComponent,
        ErrorHandlerComponent,
        ButtonComponent,
        InputCopyComponent
    ]
})
export class SharedModule { }
