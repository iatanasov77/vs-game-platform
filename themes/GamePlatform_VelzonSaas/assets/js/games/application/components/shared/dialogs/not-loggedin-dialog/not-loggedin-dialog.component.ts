import { Component, OnInit, Inject, Output, EventEmitter } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import templateString from './not-loggedin-dialog.component.html'

declare var $: any;

@Component({
    selector: 'app-user-not-loggedin',
    template:  templateString || 'Template Not Loaded !!!',
    styleUrls: []
})
export class UserNotLoggedInComponent implements OnInit
{
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( NgbModal ) private ngbModal: NgbModal,
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
}