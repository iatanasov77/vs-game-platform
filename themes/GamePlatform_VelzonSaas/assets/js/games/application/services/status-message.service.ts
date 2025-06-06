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
    ) {
        this.trans.use( 'en' );
    }
    
    setTextMessage( game: GameDto ): void
    {
        const myColor = this.appState.myColor.getValue();
        let message: StatusMessage;
        
        //alert( PlayerColor[game.currentPlayer] );
        if ( ! PlayerColor[game.currentPlayer] ) {
            return;
        }
        const currentColor = this.trans.instant( PlayerColor[game.currentPlayer] );
        
        if ( game && myColor === game.currentPlayer ) {
            this.appState.hideBusy();
            const m = this.trans.instant( 'statusmessage.yourturn', {
                color: currentColor
            });
            message = StatusMessage.info( m );
        } else {
            this.appState.hideBusy();
            const m = this.trans.instant( 'statusmessage.waitingfor', {
                color: currentColor
            });
            message = StatusMessage.info( m );
        }
        this.appState.statusMessage.setValue( message );
    }
    
    setMyConnectionLost( reason: string ): void
    {
        const m = this.trans.instant( 'statusmessage.noconnection' );
        const statusMessage = StatusMessage.error( reason || m );
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setOpponentConnectionLost(): void
    {
        const m = this.trans.instant( 'statusmessage.opponentconnectionlost' );
        const statusMessage = StatusMessage.warning( m );
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setWaitingForConnect(): void
    {
        const m = this.trans.instant( 'statusmessage.waitingoppcnn' );
        const statusMessage = StatusMessage.info( m );
        this.appState.showBusyNoOverlay();
        this.appState.statusMessage.setValue( statusMessage );
    }
    
    setGameEnded( game: GameDto, newScore: NewScoreDto ): void
    {
        const myColor = this.appState.myColor.getValue();
        let score = '';
        if ( newScore ) {
            let increase = newScore.increase.toString();
            if ( newScore.increase > 0 ) increase = `+${newScore.increase}`;
            score = this.trans.instant( 'statusmessage.newscore', {
                score: newScore.score,
                increase: increase
            });
        }
        
        const message = StatusMessage.info(
            myColor === game.winner
                ? this.trans.instant( 'statusmessage.youwon', { score: score } )
                : this.trans.instant( 'statusmessage.youlost', { score: score } )
        );
        this.appState.statusMessage.setValue( message );
        if ( myColor === game.winner ) {
            this.sound.playWinner();
            if ( game.isGoldGame )
                setTimeout( () => {
                  this.sound.playCoin();
                }, 2000 );
        } else {
          this.sound.playLooser();
        }
    }
    
    setBlockedMessage(): void
    {
        const text = this.trans.instant( 'statusmessage.youareblocked' );
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
        const text = this.trans.instant( 'statusmessage.youarenotloggedin' );
        const msg = StatusMessage.info( text );
        this.appState.statusMessage.setValue( msg );
    }
    
    setNotRoomSelected(): void
    {
        const text = this.trans.instant( 'statusmessage.youmustselectroom' );
        const msg = StatusMessage.info( text );
        this.appState.statusMessage.setValue( msg );
    }
    
    setNotGameStarted(): void
    {
        const text = this.trans.instant( 'statusmessage.gamenotstarted' );
        const msg = StatusMessage.info( text );
        this.appState.statusMessage.setValue( msg );
    }
}
