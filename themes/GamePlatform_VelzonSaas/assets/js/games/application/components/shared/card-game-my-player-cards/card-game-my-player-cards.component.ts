import { Component, Inject } from '@angular/core';

import { GameVariant } from '@vankosoft/game-platform';
import { CardGameDto } from '@vankosoft/game-platform';
import { CardDto } from '@vankosoft/game-platform';

import { AppStateService } from '../../../state/app-state.service';

import cssString from './card-game-my-player-cards.component.scss';
import templateString from './card-game-my-player-cards.component.html';

@Component({
    selector: 'card-game-my-player-cards',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssString || 'Game CSS Not Loaded !!!',
    ]
})
export class CardGameMyPlayerCardsComponent
{
    game: CardGameDto;
    myCards: CardDto[];
    
    imagesPath: string;
    
    constructor(
        @Inject( AppStateService ) private appStateService: AppStateService,
    ) {
        this.game       = this.appStateService.cardGame.getValue();
        let playerCards = this.appStateService.playerCards.getValue();
        
        this.myCards    = playerCards[this.game.currentPlayer];
        //alert( `Player Cards: ${JSON.stringify( this.myCards )}` );
        
        switch ( this.game.gameCode ) {
            case GameVariant.BRIDGE_BELOTE_CODE:
                this.imagesPath =  '/build/gameplatform-velzonsaas-theme/images/Cards/BridgeBelote';
                break;
            default:
                this.imagesPath =  '/build/gameplatform-velzonsaas-theme/images/Cards/ContractBridge';
        }
    }
}