import {
    Component,
    ElementRef,
    Input,
    OnChanges,
    ViewChild
} from '@angular/core';

import cssString from './input-copy.component.scss';
import templateString from './input-copy.component.html';

@Component({
    selector: 'app-input-copy',
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'Game CSS Not Loaded !!!']
})
export class InputCopyComponent implements OnChanges
{
    constructor() {}
    
    @ViewChild( 'linkText', { static: false } ) linkText: ElementRef | undefined;
    @Input() text: string | null = '';
    
    ngOnChanges()
    {
        if ( this.text ) {
            setTimeout( () => {
                this.selectAndCopy();
            }, 1 );
        }
    }
    
    selectAndCopy(): void
    {
        setTimeout( () => {
            const input = this.linkText?.nativeElement as HTMLInputElement;
            input.focus();
            input.select();
            input.setSelectionRange( 0, 99999 ); /* For mobile devices */
            navigator.clipboard.writeText( input.value );
            // document.execCommand('copy');
        }, 1 );
    }
}
