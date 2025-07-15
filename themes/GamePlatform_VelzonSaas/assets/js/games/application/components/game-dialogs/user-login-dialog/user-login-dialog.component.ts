import { Component, OnInit, Inject, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { AuthService } from '../../../services/auth.service';

import cssString from './user-login-dialog.component.scss';
import templateString from './user-login-dialog.component.html';

declare var $: any;

@Component({
    selector: 'app-user-login-dialog',
    template:  templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'Game CSS Not Loaded !!!'],
})
export class UserLoginDialogComponent implements OnInit
{
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    showSpinner = false;
    errorFetcingData = false;
    
    constructor(
        @Inject(Router) private router: Router,
        @Inject(AuthService) private authService: AuthService,
        
        @Inject(NgbModal) private ngbModal: NgbModal,
        public activeModal: NgbActiveModal
    ) { }
    
    ngOnInit(): void
    {
    }
    
    dismissModal(): void
    {
        //this.activeModal.dismiss();
        this.closeModal.emit();
    }
    
    handleSubmit(): void
    {
        let username    = $( '#FormUsername' ).val();
        let password    = $( '#FormPassword' ).val();
        if ( ! username || ! password ) {
            return;
        }
        
        this.showSpinner    = true;
        this.authService.login( username, password ).subscribe({
            next: ( response: any ) => {
                this.showSpinner    = false;
                this.closeModal.emit();
            },
            error: ( err: any ) => {
                this.showSpinner        = false;
                this.errorFetcingData   = true;
                console.error( err );
            }
        });
    }
}
