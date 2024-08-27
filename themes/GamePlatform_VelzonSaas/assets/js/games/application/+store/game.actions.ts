import { createAction, props } from "@ngrx/store";

import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import { IGame } from '../interfaces/game';
import { IPlayer } from '../interfaces/player';

const actionTypes = {
    startGame:                  'START_GAME',
    startGameSuccess:           'START_GAME_SUCCESS',
    startGameFailure:           'START_GAME_FAILURE',
    
    playerAnnounce:             'PLAYER_ANNOUNCE',
    playerAnnounceSuccess:      'PLAYER_ANNOUNCE_SUCCESS',
    playerAnnounceFailure:      'PLAYER_ANNOUNCE_FAILURE',
    
    loadGame:                   'LOAD_GAME',
    loadGameBySlug:             'LOAD_GAME_BY_SLUG',
    loadGameSuccess:            'LOAD_GAME_SUCCESS',
    loadGameFailure:            'LOAD_GAME_FAILURE',
    
    loadPlayers:                'LOAD_PLAYERS',
    loadPlayersSuccess:         'LOAD_PLAYERS_SUCCESS',
    loadPlayersFailure:         'LOAD_PLAYERS_FAILURE',
};

export const startGame                  = createAction( actionTypes.startGame, props<{ game: any }>() );
export const startGameSuccess           = createAction( actionTypes.startGameSuccess, props<{ cardGame: ICardGame }>() );
export const startGameFailure           = createAction( actionTypes.startGameFailure, props<{ error: any }>() );

export const playerAnnounce             = createAction( actionTypes.playerAnnounce );
export const playerAnnounceSuccess      = createAction( actionTypes.playerAnnounceSuccess, props<{ announce: ICardGameAnnounce }>() );
export const playerAnnounceFailure      = createAction( actionTypes.playerAnnounceFailure, props<{ error: any }>() );

export const loadGame                   = createAction( actionTypes.loadGame, props<{ id: number }>() );
export const loadGameBySlug             = createAction( actionTypes.loadGameBySlug, props<{ slug: string }>() );
export const loadGameSuccess            = createAction( actionTypes.loadGameSuccess, props<{ game: IGame }>() );
export const loadGameFailure            = createAction( actionTypes.loadGameFailure, props<{ error: any }>() );

export const loadPlayers                = createAction( actionTypes.loadPlayers );
export const loadPlayersSuccess         = createAction( actionTypes.loadPlayersSuccess, props<{ players: IPlayer[] }>() );
export const loadPlayersFailure         = createAction( actionTypes.loadPlayersFailure, props<{ error: any }>() );
