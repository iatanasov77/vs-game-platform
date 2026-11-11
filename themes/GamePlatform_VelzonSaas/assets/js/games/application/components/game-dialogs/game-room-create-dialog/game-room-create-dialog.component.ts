import { Component, Inject, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';

import {
    Keys,
    GameVariant,
    IPlayer,
    PlayerPosition,
    PlayerColor,
    InviteResponseDto
} from '@vankosoft/game-platform';

import { GamePlayService } from '../../../services/game-play.service';

import cssString from './game-room-create-dialog.component.scss';
import templateString from './game-room-create-dialog.component.html';

declare var $: any;

@Component({
    standalone: false,
    selector: 'game-room-create-dialog',
    template:  templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class GameRoomCreateDialogComponent
{
    @Input() gameType: string | undefined;
    
    @Input() isLoggedIn: boolean = false;
    @Input() currentPlayer: IPlayer | undefined;
    
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    invite$: Observable<InviteResponseDto> | null = null;
    
    gameId?: string;
    link: string = '';
    
    public PlayerPosition = PlayerPosition;
    public PlayerColor = PlayerColor;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( GamePlayService ) private inviteService: GamePlayService,
    ) {
        
    }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    inviteCardGamePlayer( position: PlayerPosition )
    {
        alert( `Invite Player at Position: ${position}` );
        
        $( '#inviteUrlContainer' ).show();
        this.createInviteUrl();
    }
    
    joinCardGamePlayerHere( position: PlayerPosition )
    {
        alert( `Join at Position: ${position}` );
    }
    
    joinBoardGamePlayerHere( color: PlayerColor )
    {
        alert( `Join at Color: ${color}` );
    }
    
    private createInviteUrl()
    {
        const currentUrlparams = new URLSearchParams( window.location.search );
        const variant = currentUrlparams.get( 'variant' );
        
        const gameCode: string =  window.gamePlatformSettings.gameSlug;
        
        var gameVariant;
        if ( gameCode == GameVariant.BACKGAMMON_CODE ) {
            gameVariant = variant !== null ? variant : GameVariant.BACKGAMMON_NORMAL;
        }
        
        this.invite$ = this.inviteService.createInvite( gameCode, gameVariant );
        this.invite$.subscribe( res => {
            this.gameId = res.gameId;
            
            const currentUrlparams = new URLSearchParams( window.location.search );
            
            let url = new URL( window.location.href );
            url.searchParams.delete( 'gameId' );
            url.searchParams.delete( 'playAi' );
            url.searchParams.delete( 'forGold' );
            
            let variant = currentUrlparams.get( 'variant' );
            if ( variant == null ) {
                variant = GameVariant.BACKGAMMON_NORMAL;
                url.searchParams.append( 'variant', variant );
            }
            
            url.searchParams.append( Keys.inviteId, this.gameId );
            
            this.link = url.href;
        });
    }
}