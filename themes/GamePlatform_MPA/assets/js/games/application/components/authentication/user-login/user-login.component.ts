import { Component, OnInit, Inject, Output, EventEmitter } from '@angular/core';
import { NgForm } from '@angular/forms';
import { Router } from '@angular/router';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { UserRegisterComponent } from '../user-register/user-register.component';

import templateString from './user-login.component.html'

import { ApiService } from '../../../services/api.service';
import { AuthService } from '../../../services/auth.service';

declare var $: any;

@Component({
    selector: 'app-user-login',
    template:  templateString || 'Template Not Loaded !!!',
    styleUrls: []
})
export class UserLoginComponent implements OnInit
{
    @Output() closeModalLogin: EventEmitter<any> = new EventEmitter();
    
    showSpinner = false;
    errorFetcingData = false;
    
    constructor(
        @Inject(ApiService) private apiService: ApiService,
        @Inject(Router) private router: Router,
        @Inject(AuthService) private authStore: AuthService,
        
        @Inject(NgbModal) private ngbModal: NgbModal,
        public activeModal: NgbActiveModal
    ) { }
    
    ngOnInit(): void
    {
    }
    
    dismissModal(): void
    {
        //this.activeModal.dismiss();
        this.closeModalLogin.emit();
    }
    
    handleSubmit( form: NgForm ): void
    {
        if ( form.invalid ) {
            return;
        }
        
        this.showSpinner    = true;
        let credentials = form.value;
        this.apiService.login( credentials ).subscribe({
            next: ( response: any ) => {
                let data    = response.payload;
                
                this.authStore.createAuth({
                    id: data.userId,
                    email: data.email,
                    username: data.username,
                    
                    fullName: data.userFullName,
                    
                    apiToken: data.token,
                    tokenCreated: data.tokenCreated,
                    tokenExpired: data.tokenExpired
                });
                
                this.showSpinner    = false;
                this.closeModalLogin.emit();
            },
            error: ( err: any ) => {
                this.showSpinner        = false;
                this.errorFetcingData   = true;
                console.error( err );
            }
        });
    };
    
    onClickRegistration(): void
    {
        this.closeModalLogin.emit();
        
        const modalRef = this.ngbModal.open( UserRegisterComponent );
        modalRef.componentInstance.closeModalRegister.subscribe( () => {
            // https://stackoverflow.com/questions/19743299/what-is-the-difference-between-dismiss-a-modal-and-close-a-modal-in-angular
            modalRef.dismiss();
        });
    };
}
