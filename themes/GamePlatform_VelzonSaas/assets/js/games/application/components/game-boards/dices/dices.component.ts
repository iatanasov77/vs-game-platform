import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';

import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';

import templateString from './dices.component.html';
import styleString from './dices.component.scss';

@Component({
    selector: 'app-dices',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [styleString || 'CSS Not Loaded !!!']
})
export class DicesComponent implements OnChanges
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
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'dices':
                    this.dices = changedProp.currentValue;
                    break;
                case 'color':
                    this.color = changedProp.currentValue;
                    break;
            }
        }
    }
}
