import IGameRoom from '../Model/GameRoomModel';
import CardGamePlayer from './CardGamePlayer';
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
    
    constructor( id: string, publicRootPath: string, boardSelector: string )
    {
        if ( this.constructor == AbstractGame ) {
            throw new Error( "Abstract classes can't be instantiated." );
        }
        
        this.id             = id;
        this.publicRootPath = publicRootPath;
        this.boardSelector  = boardSelector;
        this.room           = null;
        this.players        = new GamePlayersIterator( [], false );
    }
    
    public initPlayers(): Array<CardGamePlayer>
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
    
    /**
     * Used in Card Games Only
     */
    public getHands(): any
    {
        throw new Error( "Method 'getHands()' must be implemented." );
    }
    
    public setRoom( room?: IGameRoom ): void
    {
        if ( room ) {
            this.room   = room;
        }
    }
}

export default AbstractGame;
