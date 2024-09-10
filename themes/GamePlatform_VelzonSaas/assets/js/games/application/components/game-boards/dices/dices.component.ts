import { Component, Input } from '@angular/core';

import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';

import templateString from './dices.component.html';
import styleString from './dices.component.scss';

@Component({
    selector: 'app-dices',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class DicesComponent
{
    @Input() dices: DiceDto[] | null = [];
    @Input() color: PlayerColor | null = PlayerColor.neither;
    
    PlayerColor = PlayerColor;
    
    faClass = [
        'fas fa-dice-one',
        'fas fa-dice-two',
        'fas fa-dice-three',
        'fas fa-dice-four',
        'fas fa-dice-five',
        'fas fa-dice-six'
    ];
}
