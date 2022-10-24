/**
 * Abstract Class AbstractGameRules.
 * Manual: https://stackoverflow.com/questions/597769/how-do-i-create-an-abstract-base-class-in-javascript
 *         https://www.freecodecamp.org/news/how-javascript-implements-oop/
 *
 * @class AbstractGameRules
 */
class AbstractGameRules
{
    constructor()
    {
        if ( this.constructor == AbstractGameRules ) {
            throw new Error( "Abstract classes can't be instantiated." );
        }
    }

    rules( announce )
    {
        throw new Error( "Method 'rules()' must be implemented." );
    }
}

export default AbstractGameRules;
