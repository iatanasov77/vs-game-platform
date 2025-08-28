import {
    Component,
    Inject,
    EventEmitter,
    Input,
    OnChanges,
    Output,
    SimpleChanges
} from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup } from '@angular/forms';

// App State
import { AppStateService } from '../../../state/app-state.service';
import { ErrorState } from '../../../state/ErrorState';
import ErrorReportDto from '_@/GamePlatform/Model/Core/errorReportDto';

import cssString from './error-handler.component.scss';
import templateString from './error-handler.component.html';

@Component({
    selector: 'app-error-handler',
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class ErrorHandlerComponent implements OnChanges
{
    textVisible = false;
    @Input() errors: ErrorState | null = new ErrorState( '' );
    @Output() save = new EventEmitter<ErrorReportDto>();
    formGroup: UntypedFormGroup;
    
    constructor(
        @Inject( UntypedFormBuilder ) private fb: UntypedFormBuilder,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        this.showErrors.bind( this );
        this.formGroup = this.fb.group({
            errors: ['']
        });
    }
    
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ngOnChanges(changes: SimpleChanges): void
    {
        // console.log( changes );
        this.setTextAreaValue();
    }
    
    showErrors(): void
    {
        this.textVisible = true;
        this.setTextAreaValue();
    }
    
    setTextAreaValue(): void
    {
        this.formGroup.patchValue( { errors: this.errors?.message } );
    }
    
    sendErrors(): void
    {
        this.clearErrors();
        const dto: ErrorReportDto = {
            error: this.formGroup.get( 'errors' )?.value,
            reproduce: ''
        };
        
        this.save.emit( dto );
    }
    
    clearErrors(): void
    {
        this.textVisible = false;
        this.appState.errors.clearValue();
    }
}
