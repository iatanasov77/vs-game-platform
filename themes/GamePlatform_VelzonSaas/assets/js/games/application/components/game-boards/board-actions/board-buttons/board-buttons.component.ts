import { Component, Inject, OnInit, OnChanges, SimpleChanges, EventEmitter, Input, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import { Keys } from '../../../../utils/keys';

import cssString from './board-buttons.component.scss';
import templateString from './board-buttons.component.html';

@Component({
    selector: 'app-board-buttons',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'CSS Not Loaded !!!',
    ]
})
export class BoardButtonsComponent implements OnInit, OnChanges
{
    @Input() isLoggedIn: boolean        = false;
    @Input() lobbyButtonsVisible        = false;
    @Input() tutorial                   = false;
    
    @Input() newVisible = false;
    @Input() exitVisible = false;
    @Input() newRoundVisible = false;
    @Input() debugGameSoundsVisible = false;
    
    @Output() onLogin = new EventEmitter<void>();
    
    @Output() onNew = new EventEmitter<void>();
    @Output() onNewRound = new EventEmitter<void>();
    @Output() onExit = new EventEmitter<void>();
    @Output() onOpenDebugGameSoundsDialog = new EventEmitter<void>();
    
    @Output() onPlayWithComputer = new EventEmitter<string>();
    @Output() onPlayGame = new EventEmitter<string>();
    @Output() onInviteFriend = new EventEmitter<void>();
    @Output() onAcceptInvite = new EventEmitter<string>();
    @Output() onCancelInvite = new EventEmitter<void>();
    
    @Output() onRotate = new EventEmitter<void>();
    @Output() onFlip = new EventEmitter<void>();
    @Output() onResign = new EventEmitter<void>();
    
    gameCode = null;
    inviteId = null;
    acceptInviteVisible: boolean = false;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
    ) {
        
    }
    
    ngOnInit(): void
    {
        this.gameCode = window.gamePlatformSettings.gameSlug;
        this.inviteId = window.gamePlatformSettings.queryParams[
            Keys.inviteId
        ];
        
        if ( this.isLoggedIn && this.inviteId ) {
            this.acceptInviteVisible = true;
        }
    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'debugGameSoundsVisible':
                    this.debugGameSoundsVisible = changedProp.currentValue;
                    break;
                case 'isLoggedIn':
                    this.isLoggedIn = changedProp.currentValue;
                    if ( this.isLoggedIn && this.inviteId ) {
                        this.acceptInviteVisible = true;
                    }
                    break;
                case 'lobbyButtonsVisible':
                    this.lobbyButtonsVisible = changedProp.currentValue;
                    break;
                case 'newVisible':
                    this.newVisible = changedProp.currentValue;
                    break;
                case 'exitVisible':
                    this.exitVisible = changedProp.currentValue;
                    break;
                case 'newRoundVisible':
                    this.newRoundVisible = changedProp.currentValue;
                    break;
            }
        }
    }
    
    debugGameSounds(): void
    {
        this.onOpenDebugGameSoundsDialog.emit();
    }
    
    loginClick(): void
    {
        this.onLogin.emit();
    }
    
    newGame(): void
    {
        this.onNew.emit();
    }
    
    newRound(): void
    {
        this.onNewRound.emit();
    }
    
    exitGame(): void
    {
        this.onExit.emit();
    }
    
    playWithComputer(): void
    {
        this.onPlayWithComputer.emit( '' );
    }
    
    playGame(): void
    {
        this.onPlayGame.emit( '' );
    }
    
    inviteFriendClick(): void
    {
        this.onInviteFriend.emit();
    }
    
    startInvitedGame( id: string ): void
    {
        this.onAcceptInvite.emit( id );
    }
    
    cancelInvite(): void
    {
        this.onCancelInvite.emit();
    }
    
    flipClick(): void
    {
        this.onFlip.emit();
    }
    
    rotateClick(): void
    {
        this.onRotate.emit();
    }
    
    resignClick(): void
    {
        this.onResign.emit();
    }
}
