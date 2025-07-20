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
import { Router } from '@angular/router';
import { Observable } from 'rxjs';

import { Keys } from '../../../utils/keys';
import { GamePlayService } from '../../../services/game-play.service';
import { InviteResponseDto } from '../../../dto/rest/inviteResponseDto';

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
    @Output() onPlayGame = new EventEmitter<void>();
    @ViewChild( 'linkText', { static: false } ) linkText: ElementRef | undefined;
    
    invite$: Observable<InviteResponseDto> | null = null;
    
    gameId?: string;
    link: string = '';
    
    constructor(
        @Inject( GamePlayService ) private inviteService: GamePlayService,
        @Inject( Router ) private router: Router
    ) {
        const currentUrlparams = new URLSearchParams( window.location.search );
        const variant = currentUrlparams.get( 'variant' );
        
        const gameCode: string =  window.gamePlatformSettings.gameSlug;
        const gameVariant: string = variant !== null ? variant : 'normal';
        
        this.invite$ = this.inviteService.createInvite( gameCode, gameVariant );
        
        this.invite$.subscribe( res => {
            this.gameId = res.gameId;
            
            const currentUrlparams = new URLSearchParams( window.location.search );
            let variant = currentUrlparams.get( 'variant' );
            if ( variant == null ) {
                variant = 'normal';
            }
            
            let url = new URL( window.location.href );
            url.searchParams.append( 'variant', variant );
            url.searchParams.append( Keys.inviteId, this.gameId );
            this.link = url.href;
        });
    }

    startClick(): void
    {
        if ( this.gameId ) {
            const currentUrlparams = new URLSearchParams( window.location.search );
            let variant = currentUrlparams.get( 'variant' );
            if ( variant == null ) {
                variant = 'normal';
            }
            
            const urlTree = this.router.createUrlTree([], {
                queryParams: { variant: variant, gameId: this.gameId },
                queryParamsHandling: "merge",
                preserveFragment: true
            });
            this.router.navigateByUrl( urlTree );
            
            this.onPlayGame.emit();
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
