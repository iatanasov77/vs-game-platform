import { createAction, props } from "@ngrx/store";

import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import IGamePlay from '_@/GamePlatform/Model/GamePlayInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';

import IGame from '_@/GamePlatform/Model/GameInterface';
import IPlayer from '_@/GamePlatform/Model/PlayerInterface';
import IGameRoom from '_@/GamePlatform/Model/GameRoomInterface';

const actionTypes = {
    loadGame:                   'LOAD_GAME',
    loadGameBySlug:             'LOAD_GAME_BY_SLUG',
    loadGameSuccess:            'LOAD_GAME_SUCCESS',
    loadGameFailure:            'LOAD_GAME_FAILURE',
    
    loadPlayers:                'LOAD_PLAYERS',
    loadPlayersSuccess:         'LOAD_PLAYERS_SUCCESS',
    loadPlayersFailure:         'LOAD_PLAYERS_FAILURE',
    
    loadGameRooms:              'LOAD_GAME_ROOMS',
    loadGameRoomsSuccess:       'LOAD_GAME_ROOMS_SUCCESS',
    loadGameRoomsFailure:       'LOAD_GAME_ROOMS_FAILURE',
    
    selectGameRoom:             'SELECT_GAME_ROOM',
    selectGameRoomSuccess:      'SELECT_GAME_ROOM_SUCCESS',
    selectGameRoomFailure:      'SELECT_GAME_ROOM_FAILURE',
    
    startCardGame:              'START_CARD_GAME',
    startCardGameSuccess:       'START_CARD_GAME_SUCCESS',
    startCardGameFailure:       'START_CARD_GAME_FAILURE',
    
    playerAnnounce:             'PLAYER_ANNOUNCE',
    playerAnnounceSuccess:      'PLAYER_ANNOUNCE_SUCCESS',
    playerAnnounceFailure:      'PLAYER_ANNOUNCE_FAILURE',
};

export const loadGame                   = createAction( actionTypes.loadGame, props<{ id: number }>() );
export const loadGameBySlug             = createAction( actionTypes.loadGameBySlug, props<{ slug: string }>() );
export const loadGameSuccess            = createAction( actionTypes.loadGameSuccess, props<{ game: IGame }>() );
export const loadGameFailure            = createAction( actionTypes.loadGameFailure, props<{ error: any }>() );

export const loadPlayers                = createAction( actionTypes.loadPlayers );
export const loadPlayersSuccess         = createAction( actionTypes.loadPlayersSuccess, props<{ players: IPlayer[] }>() );
export const loadPlayersFailure         = createAction( actionTypes.loadPlayersFailure, props<{ error: any }>() );

export const loadGameRooms              = createAction( actionTypes.loadGameRooms, props<{ gameSlug: string }>() );
export const loadGameRoomsSuccess       = createAction( actionTypes.loadGameRoomsSuccess, props<{ rooms: IGameRoom[] }>() );
export const loadGameRoomsFailure       = createAction( actionTypes.loadGameRoomsFailure, props<{ error: any }>() );

export const selectGameRoom             = createAction( actionTypes.selectGameRoom, props<{ game: IGame; room: IGameRoom }>() );
export const selectGameRoomSuccess      = createAction( actionTypes.selectGameRoomSuccess, props<{ game: IGame }>() );
export const selectGameRoomFailure      = createAction( actionTypes.selectGameRoomFailure, props<{ error: any }>() );

export const startCardGame              = createAction( actionTypes.startCardGame, props<{ game: IGame }>() );
export const startCardGameSuccess       = createAction( actionTypes.startCardGameSuccess, props<{ gamePlay: IGamePlay }>() );
export const startCardGameFailure       = createAction( actionTypes.startCardGameFailure, props<{ error: any }>() );

export const playerAnnounce             = createAction( actionTypes.playerAnnounce );
export const playerAnnounceSuccess      = createAction( actionTypes.playerAnnounceSuccess, props<{ announce: ICardGameAnnounce }>() );
export const playerAnnounceFailure      = createAction( actionTypes.playerAnnounceFailure, props<{ error: any }>() );
