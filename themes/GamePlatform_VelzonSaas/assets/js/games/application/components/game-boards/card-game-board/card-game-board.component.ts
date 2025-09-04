import { Component, Inject, OnInit, OnDestroy, Input, OnChanges, SimpleChanges } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Actions, ofType } from '@ngrx/effects';
import { Observable, of } from 'rxjs';

import {
    selectGameRoomSuccess
} from '../../../+store/game.actions';
import { GameState } from '../../../+store/game.reducers';

import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import IGamePlayer from '_@/GamePlatform/Model/GamePlayerModel';

import templateString from './card-game-board.component.html'
import styleString from './card-game-board.component.scss'
declare var $: any;

@Component({
    selector: 'card-game-board',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class CardGameBoardComponent implements OnInit, OnDestroy, OnChanges
{
    @Input() developementClass: string  = '';
    @Input() gameProvider?: any;
    @Input() game?: any;
    @Input() gamePlayers?: Observable<IGamePlayer[]>;
    
    appState?: GameState;
    gameStarted: boolean                = false;
    gameAnnounceIcon: any;
    
    isRoomSelected: boolean             = false;
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( Actions ) private actions$: Actions,
    ) {
        this.gameAnnounceIcon   = null;
    }
    
    ngOnInit(): void
    {
        this.game?.initBoard();
        this.listenForGameEvents();
    }
    
    ngOnDestroy(): void
    {

    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        for ( const propName in changes ) {
            const changedProp = changes[propName];
            
            switch ( propName ) {
                case 'developementClass':
                    this.developementClass = changedProp.currentValue;
                    break;
            }
        }
    }
    
    listenForGameEvents(): void
    {
        $( "#card-table" ).get( 0 ).addEventListener( GameEvents.GAME_START_EVENT_NAME, ( event: any ) => {
            const { announceId }    = event.detail;
            
            this.gameAnnounceIcon   = this.gameProvider.getAnnounceSymbol( announceId )?.value;
            
            $( '#AnnounceContainer' ).hide();
            $( '#GameAnnounce' ).show();
        });
    }
}
