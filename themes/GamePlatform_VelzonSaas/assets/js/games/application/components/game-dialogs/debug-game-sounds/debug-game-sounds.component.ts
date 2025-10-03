import { Component, Inject, Input, Output, EventEmitter } from '@angular/core';
import { SoundService } from '../../../services/sound.service';

import templateString from './debug-game-sounds.component.html';

@Component({
    selector: 'debug-game-sounds',
    template:  templateString || 'Template Not Loaded !!!',
    styleUrls: []
})
export class DebugGameSoundsComponent
{
    @Output() closeModal: EventEmitter<any> = new EventEmitter();
    
    constructor(
        @Inject( SoundService ) private sound: SoundService,
    ) { }
    
    dismissModal(): void
    {
        this.closeModal.emit();
    }
    
    playThrowCards(): void
    {
        this.sound.playThrowCards();
    }
}
