import { Component, Inject, OnInit } from '@angular/core';
import { Observable } from 'rxjs';

import UserDto from '_@/GamePlatform/Model/BoardGame/userDto';
import { InviteResponseDto } from '../../../dto/rest/inviteResponseDto';

import { GamePlayService } from '../../../services/game-play.service';
import { AppStateService } from '../../../state/app-state.service';
import { ButtonComponent } from '../../shared/button/button.component';
import { Keys } from '../../../utils/keys';

import cssString from './invite-game.component.scss';
import templateString from './invite-game.component.html';

@Component({
    selector: 'app-invite-game',
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'Game CSS Not Loaded !!!']
})
export class InviteGameComponent implements OnInit
{
    invite$: Observable<InviteResponseDto> | null = null;
    inviteId = '';
    user$: Observable<UserDto>;
    
    constructor(
        @Inject( GamePlayService ) private inviteService: GamePlayService,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        this.invite$ = this.inviteService.createInvite();
        this.user$ = this.appState.user.observe();
    }
    
    ngOnInit(): void
    {
        this.inviteId = window.gamePlatformSettings.queryParams[
            Keys.inviteId
        ];
        //alert( this.inviteId );
    }
    
    startInvitedGame( id: string ): void
    {
        //this.router.navigateByUrl( 'game?gameId=' + id );
    }
    
    cancelInvite(): void
    {
        //this.router.navigateByUrl( 'lobby' );
    }
}
