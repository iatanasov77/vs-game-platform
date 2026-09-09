import { createAction, props } from "@ngrx/store";

import { IGamePlay } from '@vankosoft/game-platform';

import { IGame } from '@vankosoft/game-platform';
import { IPlayer } from '@vankosoft/game-platform';
import { IGameRoom } from '@vankosoft/game-platform';

const actionTypes = {
    selectGameRoom:             'SELECT_GAME_ROOM',
    selectGameRoomSuccess:      'SELECT_GAME_ROOM_SUCCESS',
    selectGameRoomFailure:      'SELECT_GAME_ROOM_FAILURE',
};

export const selectGameRoom             = createAction( actionTypes.selectGameRoom, props<{ game: IGame; room: IGameRoom }>() );
export const selectGameRoomSuccess      = createAction( actionTypes.selectGameRoomSuccess, props<{ game: IGame }>() );
export const selectGameRoomFailure      = createAction( actionTypes.selectGameRoomFailure, props<{ error: any }>() );
