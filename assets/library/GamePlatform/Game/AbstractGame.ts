import GameSettings from './GameSettings';
import IGameRoom from '../Model/GameRoomModel';
import IGamePlayer from '../Model/GamePlayerModel';
import GamePlayersIterator from './GamePlayersIterator';

/**
 * Abstract Class AbstractGame.
 * Manual: https://stackoverflow.com/questions/597769/how-do-i-create-an-abstract-base-class-in-javascript
 *         https://www.freecodecamp.org/news/how-javascript-implements-oop/
 *
 * @class AbstractGame
 */
class AbstractGame
{
    /** Game Slug */
    id: string;
    room: null | IGameRoom;
    
    /** Game Players */
    players?: GamePlayersIterator;
    
    /** Public Root Path for Assets */
    publicRootPath: string
    
    boardSelector: string;
    
    timeoutBetweenPlayers: number;
    
    /** GamePlayer That Play with this Web Session */
    player?: IGamePlayer;
    
    constructor( gameSettings: GameSettings )
    {
        if ( this.constructor == AbstractGame ) {
            throw new Error( "Abstract classes can't be instantiated." );
        }
        
        this.id                     = gameSettings.id;
        this.publicRootPath         = gameSettings.publicRootPath;
        this.boardSelector          = gameSettings.boardSelector;
        this.timeoutBetweenPlayers  = gameSettings.timeoutBetweenPlayers;
        
        this.room                   = null;
    }
    
    public initPlayers( room: IGameRoom ): void
    {
        throw new Error( "Method 'initPlayers()' must be implemented." );
    }
    
    public initBoard(): void
    {
        throw new Error( "Method 'initBoard()' must be implemented." );
    }
    
    public startGame(): void
    {
        throw new Error( "Method 'startGame()' must be implemented." );
    }
    
    public nextGame(): void
    {
        throw new Error( "Method 'nextGame()' must be implemented." );
    }
    
    public getPlayers(): Array<IGamePlayer>
    {
        return this.players ? this.players.getPlayers() : [];
    }
    
    public setRoom( room?: IGameRoom ): void
    {
        if ( room ) {
            this.room   = room;
            this.initPlayers( room );
        }
    }
}

export default AbstractGame;
