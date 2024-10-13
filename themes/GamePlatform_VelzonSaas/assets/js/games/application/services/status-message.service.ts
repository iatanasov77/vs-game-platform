import { Injectable, Inject } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { StatusMessage } from '../utils/status-message';

import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import NewScoreDto from '_@/GamePlatform/Model/BoardGame/newScoreDto';

import { SoundService } from './sound.service'
import { AppStateService } from '../state/app-state.service';

@Injectable({
    providedIn: 'root'
})
export class StatusMessageService
{
    constructor(
        @Inject( TranslateService ) private trans: TranslateService,
        @Inject( SoundService ) private sound: SoundService,
        @Inject( AppStateService ) private appState: AppStateService,
    ) { }
    
    setTextMessage( game: GameDto ): void
    {
        const myColor = this.appState.myColor.getValue();
        let message: StatusMessage;
        if ( game && myColor === game.currentPlayer ) {
            this.appState.hideBusy();
            message = StatusMessage.info( `Your turn to move.  ( ${PlayerColor[game.currentPlayer]} )` );
        } else {
            this.appState.hideBusy();
            message = StatusMessage.info( `Waiting for ${PlayerColor[game.currentPlayer]} to move.` );
        }
        this.appState.statusMessage.setValue( message );
    }
    
    setMyConnectionLost( reason: string ): void
    {
        const statusMessage = StatusMessage.error( reason || 'No server connection' );
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setOpponentConnectionLost(): void
    {
        const statusMessage = StatusMessage.warning( 'Opponent connection lost' );
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setWaitingForConnect(): void
    {
        const statusMessage = StatusMessage.info( 'Waiting for opponent to connect' );
        this.appState.showBusyNoOverlay();
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setGameEnded( game: GameDto, newScore: NewScoreDto ): void
    {
        // console.log(this.myColor, this.game.winner);
        const myColor = this.appState.myColor.getValue();
        let message = StatusMessage.info( 'Game ended.' );
        if ( newScore ) {
            const score = `New score ${newScore.score} (${newScore.increase})`;
            
            message = StatusMessage.info(
                myColor === game.winner ? `Congrats! You won. ${score}` : `Sorry. You lost the game. ${score}`
            );
            this.appState.statusMessage.setValue( message );
        }
    }
    
    setBlockedMessage(): void
    {
        const text = 'You are blocked. Click "Done"';
        const msg = StatusMessage.warning( text );
        this.appState.statusMessage.setValue( msg );
    }
    
    setMoveNow(): void
    {
        const m = this.trans.instant( 'statusmessage.movenow' );
        const message = StatusMessage.warning( m );
        this.sound.playWarning();
        this.appState.statusMessage.setValue( message );
    }
    
    setDoublingAccepted()
    {
        const text = this.trans.instant( 'statusmessage.dblaccepted' );
        const msg = StatusMessage.info( text );
        this.appState.statusMessage.setValue( msg );
    }
    
    setDoublingRequested()
    {
        const text = this.trans.instant( 'statusmessage.dblrequested' );
        const msg = StatusMessage.info( text );
        this.appState.statusMessage.setValue( msg );
    }
    
    setWaitingForDoubleResponse()
    {
        const text = this.trans.instant( 'statusmessage.waitfordblresponse' );
        const msg = StatusMessage.info( text );
        this.appState.statusMessage.setValue( msg );
    }
    
    /**
     * Vankata Statuses
     */
     
    setNotLoggedIn(): void
    {
        const statusMessage = StatusMessage.info( 'You are NOT Logged in' );
        this.appState.showBusyNoOverlay();
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setNotRoomSelected(): void
    {
        const statusMessage = StatusMessage.info( 'You must select a Room' );
        this.appState.showBusyNoOverlay();
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setNotGameStarted(): void
    {
        const statusMessage = StatusMessage.info( 'Game is NOT Started' );
        this.appState.showBusyNoOverlay();
        this.appState.statusMessage.setValue( statusMessage );
    }
}
