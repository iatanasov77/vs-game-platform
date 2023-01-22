/**
 * Abstract Class AbstractGame.
 * Manual: https://stackoverflow.com/questions/597769/how-do-i-create-an-abstract-base-class-in-javascript
 *         https://www.freecodecamp.org/news/how-javascript-implements-oop/
 *
 * @class AbstractGame
 */
class AbstractGame
{
    boardSelector: string;
    
    constructor( boardSelector: string )
    {
        if ( this.constructor == AbstractGame ) {
            throw new Error( "Abstract classes can't be instantiated." );
        }
        
        this.boardSelector  = boardSelector;
    }
    
    public initBoard(): void
    {
        throw new Error( "Method 'initCardsDeck()' must be implemented." );
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
}

export default AbstractGame;
