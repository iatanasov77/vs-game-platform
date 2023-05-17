import { createAction, props } from "@ngrx/store";

import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import ICardGame from '_@/GamePlatform/Game/CardGameInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

const actionTypes = {
    startGame:              'START_GAME',
    startGameSuccess:       'START_GAME_SUCCESS',
    startGameFailure:       'START_GAME_FAILURE',
    
    playerAnnounce:         'PLAYER_ANNOUNCE',
    playerAnnounceSuccess:  'PLAYER_ANNOUNCE_SUCCESS',
    playerAnnounceFailure:  'PLAYER_ANNOUNCE_FAILURE',
};

export const startGame              = createAction( actionTypes.startGame );
export const startGameSuccess       = createAction( actionTypes.startGameSuccess, props<{ game: ICardGame }>() );
export const startGameFailure       = createAction( actionTypes.startGameFailure, props<{ error: any }>() );

export const playerAnnounce         = createAction( actionTypes.playerAnnounce );
export const playerAnnounceSuccess  = createAction( actionTypes.playerAnnounceSuccess, props<{ announce: ICardGameAnnounce }>() );
export const playerAnnounceFailure  = createAction( actionTypes.playerAnnounceFailure, props<{ error: any }>() );
