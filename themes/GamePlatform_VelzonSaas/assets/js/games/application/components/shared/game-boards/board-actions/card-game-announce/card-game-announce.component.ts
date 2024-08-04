import { Component, OnInit, Inject, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import templateString from './card-game-announce.component.html'

@Component({
    selector: 'card-game-announce',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: []
})
export class CardGameAnnounceComponent implements OnInit
{
    constructor(
    
    ) {
    
    }
    
    ngOnInit(): void
    {
    
    }
}