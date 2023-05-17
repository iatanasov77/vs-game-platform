import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { SharedModule } from '../shared/shared.module';

import { UserRegisterComponent } from './user-register/user-register.component';
import { UserLoginComponent } from './user-login/user-login.component';
import { UserLogoutComponent } from './user-logout/user-logout.component';

@NgModule({
    declarations: [
        UserRegisterComponent,
        UserLoginComponent,
        UserLogoutComponent
    ],
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        NgbModule,
        
        SharedModule,
    ],
    exports: [
        UserRegisterComponent,
        UserLoginComponent,
        UserLogoutComponent
    ],
    providers: [NgbActiveModal]
})
export class AuthenticationModule { }
