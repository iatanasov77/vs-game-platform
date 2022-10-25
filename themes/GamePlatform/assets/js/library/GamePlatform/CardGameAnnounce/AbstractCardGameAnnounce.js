/**
 * Abstract Class AbstractCardGameAnnounce.
 * Manual: https://stackoverflow.com/questions/597769/how-do-i-create-an-abstract-base-class-in-javascript
 *         https://www.freecodecamp.org/news/how-javascript-implements-oop/
 *
 * @class AbstractCardGameAnnounce
 */
class AbstractCardGameAnnounce
{
    constructor()
    {
        if ( this.constructor == AbstractCardGameAnnounce ) {
            throw new Error( "Abstract classes can't be instantiated." );
        }
    }
    
    announce( hand, lastAnnounce )
    {
        throw new Error( "Method 'announce()' must be implemented." );
    }
    
    getAnnounce( announces )
    {
        throw new Error( "Method 'getAnnounce()' must be implemented." );
    }
}

export default AbstractCardGameAnnounce;
