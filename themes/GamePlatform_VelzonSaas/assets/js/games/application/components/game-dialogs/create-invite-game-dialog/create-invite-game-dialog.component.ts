import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    EventEmitter,
    Output,
    ViewChild,
    Inject
} from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Keys } from '../../../utils/keys';
import { CookieService } from 'ngx-cookie-service';
import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';

import cssString from './create-invite-game-dialog.component.scss';
import templateString from './create-invite-game-dialog.component.html';

@Component({
    selector: 'app-invite',
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'Game CSS Not Loaded !!!'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CreateInviteGameDialogComponent
{
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    @Output() cancel = new EventEmitter<string>();
    @ViewChild( 'linkText', { static: false } ) linkText: ElementRef | undefined;
    
    gameId?: string;
    gameUrl: URL;
    link: string;
    
    constructor( @Inject( CookieService ) private cookieService: CookieService )
    {
        // https://stackoverflow.com/a/75840412/12693473
        let url         = new URL( window.location.href );
        this.gameUrl    = { ...url };
        
        let gameCookie  = this.cookieService.get( Keys.gameIdKey );
        if ( gameCookie ) {
            let gameCookieDto = JSON.parse( gameCookie ) as GameCookieDto;
            this.gameId = gameCookieDto.id
            url.searchParams.append( Keys.inviteId, this.gameId );
        }
    
        this.link = url.href;
    }
    
    startClick(): void
    {
        if ( this.gameId ) {
            this.gameUrl.searchParams.append( 'gameId', this.gameId );
            document.location = this.gameUrl.href;
        }
    }
    
    cancelClick(): void
    {
        if ( this.gameId ) {
            this.cancel.emit( this.gameId );
        }
        this.closeModal.emit();
    }
    
    dismissModal(): void
    {
        this.cancelClick();
    }
}
