import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    EventEmitter,
    Input,
    OnChanges,
    Output,
    ViewChild
} from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Keys } from '../../../utils/keys';

import cssString from './create-invite-game-dialog.component.scss';
import templateString from './create-invite-game-dialog.component.html';

@Component({
    selector: 'app-invite',
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'Game CSS Not Loaded !!!'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CreateInviteGameDialogComponent implements OnChanges
{
    @Input() gameId: string | undefined = '';
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    @Output() cancel = new EventEmitter<string>();
    @Output() started = new EventEmitter<string>();
    @ViewChild('linkText', { static: false }) linkText: ElementRef | undefined;
    
    link = '';
    
    ngOnChanges(): void
    {
        this.link = `${window.location.href}?${Keys.inviteId}=${this.gameId}`;
    }
    
    startClick(): void
    {
        this.started.emit( this.gameId );
    }
    
    cancelClick(): void
    {
        //this.cancel.emit( this.gameId );
        this.dismissModal();
    }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
}
