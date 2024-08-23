import { Component, OnInit, OnDestroy, Inject } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { map, merge } from 'rxjs';
import { loadPlayers, loadPlayersFailure, loadPlayersSuccess } from '../../../../+store/game.actions';
import { IPlayer } from '../../../../interfaces/player';

import templateString from './game-players.component.html'
import cssString from './game-players.component.scss'

declare var $: any;

@Component({
    selector: 'game-players',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [cssString || 'CSS Not Loaded !!!']
})
export class GamePlayersComponent implements OnInit, OnDestroy
{
    showSpinner = true;
    players: null | IPlayer[] = null;
    
    isFetchingPlayers$ = merge(
        this.actions$.pipe(
            ofType( loadPlayers ),
            map( () => true )
        ),
        this.actions$.pipe(
            ofType( loadPlayersSuccess ),
            map( () => false )
        ),
        this.actions$.pipe(
            ofType( loadPlayersFailure ),
            map( () => false )
        )
    );
    
    constructor(
        @Inject( TranslateService ) private translate: TranslateService,
        @Inject( Store ) private store: Store,
        @Inject( Actions ) private actions$: Actions
    ) {
        this.store.dispatch( loadPlayers() );
        this.store.subscribe( ( state: any ) => {
            this.showSpinner    = state.app.main.players == null;
            this.players        = state.app.main.players;
        });
    }
    
    ngOnInit(): void
    {
        
    }
    
    ngOnDestroy()
    {

    }
}
