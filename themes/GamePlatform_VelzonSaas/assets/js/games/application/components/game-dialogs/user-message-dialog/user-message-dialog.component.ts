import { Component, OnInit, OnDestroy, Inject, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { AuthService } from '../../../services/auth.service';

import cssString from './user-message-dialog.component.scss';
import templateString from './user-message-dialog.component.html';

declare var $: any;

@Component({
    standalone: false,
    selector: 'app-user-message-dialog',
    template:  templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'Game CSS Not Loaded !!!'],
})
export class UserMessageDialogComponent implements OnInit, OnDestroy
{
    @Input() message: string | undefined;
    @Input() messageQuestion: string | undefined;
    @Input() timeLeft: number = 60;
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    interval: any;
    
    constructor(
        @Inject(Router) private router: Router,
        @Inject(AuthService) private authService: AuthService,
        
        @Inject(NgbModal) private ngbModal: NgbModal,
        public activeModal: NgbActiveModal
    ) { }
    
    ngOnInit(): void
    {
        this.startTimer();
    }
    
    ngOnDestroy(): void
    {
        this.pauseTimer();
    }
    
    dismissModal(): void
    {
        this.pauseTimer();
        this.closeModal.emit();
    }
    
    exitGame(): void
    {
        this.pauseTimer();
    }
    
    startTimer(): void
    {
        this.interval = setInterval( () => {
            if( this.timeLeft > 0 ) {
                this.timeLeft--;
            } else {
                this.dismissModal();
            }
        }, 1000 );
    }
    
    pauseTimer()
    {
        clearInterval( this.interval );
    }
}
