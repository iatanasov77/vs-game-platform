import { CommonModule } from '@angular/common';
import {
    ChangeDetectorRef,
    Component,
    ElementRef,
    HostListener,
    ViewChild,
    OnInit,
    OnDestroy,
    Inject
} from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { TranslateModule } from '@ngx-translate/core';
import { map } from 'rxjs';
import { ChatService } from '../../../services/websocket/chat.service';
import { AppStateService } from '../../../state/app-state.service';

import templateString from './game-chat.component.html'
import cssString from './game-chat.component.scss'

@Component({
    selector: 'game-chat',
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GameChatComponent implements OnInit, OnDestroy
{
    formGroup: FormGroup;

    @ViewChild('msginput') input!: ElementRef;
    @ViewChild('conversation') conversation!: ElementRef;
    
    chatMessages$ = this.stateService.chatMessages.observe().pipe(
        map( ( messages ) => {
            const bld = [];
            for ( let i = 0; i < messages.length; i++ ) {
                const element = messages[i];
                
                if ( i === 0 ) {
                    bld.push( `${element.fromUser}` );
                } else if ( messages[i - 1].fromUser !== element.fromUser ) {
                    bld.push( `\n${element.fromUser}` );
                }
                bld.push( `- ${element.message}` );
            }
            return bld;
        }),
        map( ( m ) => m.join( '\n' ) )
    );
    
    users$ = this.stateService.chatUsers.observe().pipe(
        map( ( u ) => u?.map( ( v ) => v ) ),
        map( ( u ) => u?.join( '\n' ) )
    );
    
    usersCount = 0;
    othersInChat = false;
    wasInside = false;
  
    constructor(
        @Inject( AppStateService ) private stateService: AppStateService,
        @Inject( FormBuilder ) private fb: FormBuilder,
        @Inject( ChatService ) private chatService: ChatService,
        @Inject( ChangeDetectorRef ) private changeDetector: ChangeDetectorRef
    ) {
        this.formGroup = this.fb.group({
            message: ['']
        });
        
        // todo: get this to work with observables
        setInterval( () => {
            this.usersCount = this.stateService.chatUsers.getValue().length;
            this.othersInChat = this.usersCount > 1;
        }, 2000 );
        
        this.chatMessages$.subscribe( () => {
            setTimeout( () => {
                const elements = document.getElementsByClassName( 'conversation' );
                if ( elements.length > 0 ) {
                    const textarea = elements[0]!;
                    textarea.scrollTop = textarea.scrollHeight;
                }
            }, 1 );
        });
        
        
    }
    
    ngOnInit(): void
    {
        if ( this.isLoggedIn() ) {
            this.changeDetector.detectChanges();
            this.chatService.connect();
        }
    }
    
    ngOnDestroy()
    {
        if ( this.isLoggedIn() ) {
            this.stateService.chatMessages.setValue( [] );
            this.changeDetector.detectChanges();
            this.chatService.disconnect();
        }
    }
    
    onSubmit()
    {
        const ctrl = this.formGroup.get( 'message' );
        const message = ctrl?.value;
        if ( message?.trim() ) {
            ctrl?.setValue( '' );
            this.chatService.sendMessage( message );
        }
    }
    
    @HostListener( 'click' )
    clickInside()
    {
        this.wasInside = true;
    }
    
    @HostListener( 'document:click' )
    clickout()
    {
        this.wasInside = false;
    }
    
    isLoggedIn(): boolean
    {
        return !! this.stateService.user.getValue();
    }
}
