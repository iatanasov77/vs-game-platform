import {
    Component,
    Inject,
    EventEmitter,
    Input,
    AfterViewInit,
    OnChanges,
    Output,
    SimpleChanges
} from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';

// App State
import { AppState } from '../../../state/app-state';
import { ErrorState } from '../../../state/ErrorState';
import ErrorReportDto from '_@/GamePlatform/Model/BoardGame/errorReportDto';

import cssString from './error-handler.component.scss';
import templateString from './error-handler.component.html';

@Component({
    selector: 'app-error-handler',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
    
    // changeDetection: ChangeDetectionStrategy.OnPush
})
export class ErrorHandlerComponent implements AfterViewInit, OnChanges
{
    textVisible = false;
    @Input() errors: ErrorState | null = new ErrorState( '' );
    @Output() save = new EventEmitter<ErrorReportDto>();
    formGroup: FormGroup;
    
    constructor( @Inject( FormBuilder ) private fb: FormBuilder )
    {
        this.showErrors.bind( this );
        this.formGroup = fb.group({
            errors: ['']
        });
    }
    
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ngOnChanges(changes: SimpleChanges): void
    {
        // console.log( changes );
        this.setTextAreaValue();
    }
    
    ngAfterViewInit(): void
    {
        // setInterval(() => {
        //   throw new Error( 'interval' );
        // }, 3000);
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
        AppState.Singleton.errors.clearValue();
    }
}
