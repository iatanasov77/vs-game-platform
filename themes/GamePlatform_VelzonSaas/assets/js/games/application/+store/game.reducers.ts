import { ActionReducerMap, createReducer, on } from "@ngrx/store";

import {
    selectGameRoom,
    selectGameRoomSuccess,
} from "./game.actions";

import { IGamePlay } from '@vankosoft/game-platform';

import { IGame } from '@vankosoft/game-platform';
import { IPlayer } from '@vankosoft/game-platform';
import { IGameRoom } from '@vankosoft/game-platform';

export interface GameState
{
    game:           null | IGame;
    players:        null | IPlayer[];
    rooms:          null | IGameRoom[];
    
    gamePlay:       null | IGamePlay;
}

const initialState: GameState = {
    game:           null,
    players:        null,
    rooms:          null,
    
    gamePlay:       null
};
