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
    
    startGame:                  'START_GAME',
    startGameSuccess:           'START_GAME_SUCCESS',
    startGameFailure:           'START_GAME_FAILURE',
    
    playGame:                   'PLAY_GAME',
    
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

export const loadGameRooms              = createAction( actionTypes.loadGameRooms );
export const loadGameRoomsSuccess       = createAction( actionTypes.loadGameRoomsSuccess, props<{ rooms: IGameRoom[] }>() );
export const loadGameRoomsFailure       = createAction( actionTypes.loadGameRoomsFailure, props<{ error: any }>() );

export const selectGameRoom             = createAction( actionTypes.selectGameRoom, props<{ game: IGame; room: IGameRoom }>() );
export const selectGameRoomSuccess      = createAction( actionTypes.selectGameRoomSuccess, props<{ game: IGame }>() );
export const selectGameRoomFailure      = createAction( actionTypes.selectGameRoomFailure, props<{ error: any }>() );

export const startGame                  = createAction( actionTypes.startGame, props<{ game: any }>() );
export const startGameSuccess           = createAction( actionTypes.startGameSuccess, props<{ gamePlay: IGamePlay }>() );
export const startGameFailure           = createAction( actionTypes.startGameFailure, props<{ error: any }>() );

export const playGame                   = createAction( actionTypes.playGame );

export const playerAnnounce             = createAction( actionTypes.playerAnnounce );
export const playerAnnounceSuccess      = createAction( actionTypes.playerAnnounceSuccess, props<{ announce: ICardGameAnnounce }>() );
export const playerAnnounceFailure      = createAction( actionTypes.playerAnnounceFailure, props<{ error: any }>() );
