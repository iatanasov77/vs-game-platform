import { Component, Inject, Input, Output, EventEmitter } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { AuthService } from '../../../../services/auth.service'

import templateString from './game-requirements-dialog.component.html'

declare var $: any;

@Component({
    selector: 'app-user-not-loggedin',
    template:  templateString || 'Template Not Loaded !!!',
    styleUrls: []
})
export class GameRequirementsDialogComponent
{
    @Input() isLoggedIn: boolean    = false;
    @Input() hasPlayer: boolean     = false;
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
        @Inject( AuthService ) private authService: AuthService,
        public activeModal: NgbActiveModal
    ) { }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    createPlayerForCurrentUser( event: any ): void
    {
        event.preventDefault();
        
        let auth        = this.authService.getAuth();
        if ( ! auth ) {
            return;
        }
        
        // This Called Only for Logged Users
        $.ajax({
            type: "GET",
            url: '/ajax/create-player-for-user/' + auth.id,
            success: function( response: any )
            {
                document.location = document.location;
            },
            error: function()
            {
                alert( "SYSTEM ERROR!!!" );
            }
        });
    }
}