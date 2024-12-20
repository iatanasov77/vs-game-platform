import { Injectable, Inject, OnDestroy } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { AppStateService } from '../state/app-state.service';

@Injectable({
    providedIn: 'root'
})
export class SoundService implements OnDestroy
{
    click: HTMLAudioElement;
    dice: HTMLAudioElement;
    checker: HTMLAudioElement;
    checkerWood: HTMLAudioElement;
    looser: HTMLAudioElement;
    swish: HTMLAudioElement;
    warning: HTMLAudioElement;
    winner: HTMLAudioElement;
    coin: HTMLAudioElement;
    blues: HTMLAudioElement;
    tick: HTMLAudioElement;
    pianointro: HTMLAudioElement;
    introPlaying = false;
    
    introPlaying$: BehaviorSubject<boolean>;

    constructor( @Inject( AppStateService ) private appState: AppStateService )
    {
        this.introPlaying$  = new BehaviorSubject<boolean>( this.introPlaying );
        
        this.click = new Audio();
        this.click.src = '/build/gameplatform-velzonsaas-theme/sound/click.wav';
        this.click.load();
        
        this.dice = new Audio();
        this.dice.src = '/build/gameplatform-velzonsaas-theme/sound/dice.wav';
        this.dice.load();
        
        this.checker = new Audio();
        this.checker.src = '/build/gameplatform-velzonsaas-theme/sound/checker.wav';
        this.checker.load();
        
        this.checkerWood = new Audio();
        this.checkerWood.src = '/build/gameplatform-velzonsaas-theme/sound/checker-wood.wav';
        this.checkerWood.load();
        
        this.looser = new Audio();
        this.looser.src = '/build/gameplatform-velzonsaas-theme/sound/looser.wav';
        this.looser.load();
        
        this.swish = new Audio();
        this.swish.src = '/build/gameplatform-velzonsaas-theme/sound/swish.wav';
        this.swish.load();
        
        this.warning = new Audio();
        this.warning.src = '/build/gameplatform-velzonsaas-theme/sound/warning.wav';
        this.warning.load();
        
        this.winner = new Audio();
        this.winner.src = '/build/gameplatform-velzonsaas-theme/sound/winner.wav';
        this.winner.load();
        
        this.coin = new Audio();
        this.coin.src = '/build/gameplatform-velzonsaas-theme/sound/coin.wav';
        this.coin.preload = 'auto';
        this.coin.load();
        
        this.blues = new Audio();
        this.blues.src = '/build/gameplatform-velzonsaas-theme/sound/blues.mp3';
        this.blues.preload = 'auto';
        this.blues.load();
        
        this.tick = new Audio();
        this.tick.src = '/build/gameplatform-velzonsaas-theme/sound/ticktock.mp3';
        this.tick.load();
        this.tick.volume = 0.25;
        
        this.pianointro = new Audio();
        this.pianointro.src = '/build/gameplatform-velzonsaas-theme/sound/pianointro.mp3';
        this.pianointro.load();
        this.pianointro.onended = () => {
            this.introPlaying = false;
        };
    }
    
    isIntroPlaying(): Observable<boolean>
    {
        return this.introPlaying$.asObservable();
    }
    
    ngOnDestroy()
    {
        this.fadeIntro();
    }
    
    fadeIntro()
    {
        this.fadeSound( this.blues );
        this.fadeSound( this.pianointro );
    }
    
    playClick(): void
    {
        this.click.play();
    }
    
    playDice(): void
    {
        this.dice.play();
    }
    
    playChecker(): void
    {
        this.checker.play();
    }
    
    playCheckerWood(): void
    {
        this.checkerWood.play();
    }
    
    playLooser(): void
    {
        this.looser.play();
    }
    
    playSwish(): void
    {
        this.swish.play();
    }
    
    playWarning(): void
    {
        this.warning.play();
    }
    
    playWinner(): void
    {
        this.winner.play();
    }
    
    playCoin(): void
    {
        this.coin.play();
    }
    
    playTick(): void
    {
        this.tick.play();
    }
    
    playBlues(): void
    {
        let vol = 1;
        if ( this.appState.user.getValue()?.muteIntro ) vol = 0;
        this.blues.volume = vol;
        this.blues.play();
    }
    
    playPianoIntro(): void
    {
        let vol = 0.3;
        this.introPlaying = true;
        this.introPlaying$.next( this.introPlaying );
        
        if ( this.appState.user.getValue()?.muteIntro ) vol = 0;
        
        this.pianointro.volume = vol;
        this.pianointro.play();
    }
    
    unMuteIntro(): void
    {
        this.pianointro.volume = 0.3;
        this.blues.volume = 1;
    }
    
    fadeSound( sound: HTMLAudioElement ): void
    {
        const startVol = sound.volume;
        if ( startVol === 0 ) return;
        const interval = 50; //ms
        const fadeLength = 1000;
        const fadeStep = startVol / ( fadeLength / interval );
        const handle = setInterval( () => {
            let b = sound.volume;
            b -= fadeStep;
            if ( b < 0 ) {
              b = 0;
              clearInterval( handle );
            }
            sound.volume = b;
        }, interval );
    }
}
