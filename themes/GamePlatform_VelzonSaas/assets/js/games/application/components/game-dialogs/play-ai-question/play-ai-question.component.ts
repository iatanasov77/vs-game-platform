import {
    Component,
    ChangeDetectionStrategy,
    Inject,
    EventEmitter,
    Output
} from '@angular/core';
import { ButtonComponent } from '../../shared/button/button.component';
import { TranslateService } from '@ngx-translate/core';

import cssString from './play-ai-question.component.scss';
import templateString from './play-ai-question.component.html';

@Component({
    selector: 'app-play-ai-question',
    changeDetection: ChangeDetectionStrategy.OnPush,
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class PlayAiQuestionComponent
{
    @Output() onPlayAi = new EventEmitter<void>();
    @Output() onKeepWaiting = new EventEmitter<void>();
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService
    ) {}
    
    continueWait( event: any ): void
    {
        this.onKeepWaiting.emit();
    }
    
    playAi( event: any ): void
    {
        this.onPlayAi.emit();
    }
}
