import { StatusMessage } from '../utils/status-message';

import ConnectionDto from '_@/GamePlatform/Model/BoardGame/connectionDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import GameState from '_@/GamePlatform/Model/BoardGame/gameState';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';

import Toplist from '_@/GamePlatform/Model/BoardGame/toplist';
import UserDto from '_@/GamePlatform/Model/BoardGame/userDto';

import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';

import { StateObject } from './state-object';
import { Busy } from './busy';
import { ErrorState } from './ErrorState';

export class AppState
{
    private static _singleton: AppState;
    
    busy: StateObject<Busy>;
    game: StateObject<GameDto>;
    myColor: StateObject<PlayerColor>;
    dices: StateObject<DiceDto[]>;
    moveAnimations: StateObject<MoveDto[]>;
    myConnection: StateObject<ConnectionDto>;
    opponentConnection: StateObject<ConnectionDto>;
    user: StateObject<UserDto>;
    statusMessage: StateObject<StatusMessage>;
    moveTimer: StateObject<number>;
    toplist: StateObject<Toplist>;
    errors: StateObject<ErrorState>;
    
    constructor() {
        this.busy = new StateObject<Busy>();
        this.game = new StateObject<GameDto>();
        this.myColor = new StateObject<PlayerColor>();
        this.myColor.setValue( PlayerColor.neither );
        this.dices = new StateObject<DiceDto[]>();
        this.dices.setValue([]);
        this.moveAnimations = new StateObject<MoveDto[]>();
        this.moveAnimations.setValue( [] );
        this.myConnection = new StateObject<ConnectionDto>();
        this.opponentConnection = new StateObject<ConnectionDto>();
        this.user = new StateObject<UserDto>();
        this.statusMessage = new StateObject<StatusMessage>();
        this.moveTimer = new StateObject<number>();
        this.toplist = new StateObject<Toplist>();
        this.errors = new StateObject<ErrorState>();
    }
    
    public static get Singleton(): AppState
    {
        if ( ! this._singleton ) {
            this._singleton = new AppState();
        }
        return this._singleton;
    }
    
    myTurn(): boolean
    {
        const game = this.game.getValue();
        
        return (
            game &&
            game.playState !== GameState.ended &&
            game.currentPlayer === this.myColor.getValue()
        );
    }
}
