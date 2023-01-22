import CardGamePlayer from './CardGamePlayer';

class GamePlayersIterator implements Iterator<CardGamePlayer>
{
    private currentIndex: number;
    private startIterationIndex: number;
    
    private players: Array<CardGamePlayer>;
    private clockwise: boolean;
    
    constructor( players: Array<CardGamePlayer>, clockwise: boolean = true  )
    {
        this.currentIndex           = 0;
        this.startIterationIndex    = 0;
        
        this.players                = players;
        this.clockwise              = clockwise;
    }
    
    public getPlayer( index: number )
    {
        return this.players[index];
    }
    
    public getPlayers(): Array<CardGamePlayer>
    {
        return this.players;
    }
    
    public geCurrentPlayer(): CardGamePlayer
    {
        return this.players[this.currentIndex];
    }
    
    public getCurrentIndex(): number
    {
        return this.currentIndex;
    }
    
    public setStartIterationIndex( index: number ): void
    {
        this.currentIndex           = index - 1;
        this.startIterationIndex    = index;
    }
    
    public count(): number
    {
        return this.players.length;
    }
    
    public rewind(): void
    {
        this.currentIndex   = 0;
    }
    
    public next(): IteratorResult<CardGamePlayer>
    {
        let currentPlayer   = this.players[this.currentIndex];
        this.currentIndex++;
        
        return {
            done: ( this.currentIndex == ( this.players.length ) ),
            value: currentPlayer
        };
    }
    
    public nextPlayer(): IteratorResult<CardGamePlayer>
    {
        if ( this.clockwise ) {
            return this.nextPlayerClockwise();
        } else {
            return this.nextPlayerBackClockwise();
        }
    }
    
    private nextPlayerClockwise(): IteratorResult<CardGamePlayer>
    {
        let currentPlayer   = this.players[this.currentIndex];
        this.currentIndex++;
        if ( this.currentIndex == this.players.length ) {
            this.currentIndex = 0;
        }
        
        return {
            done: ( this.currentIndex == ( this.startIterationIndex - 1 ) ),
            value: currentPlayer
        };
    }
    
    private nextPlayerBackClockwise(): IteratorResult<CardGamePlayer>
    {
        let currentPlayer   = this.players[this.currentIndex];
        this.currentIndex--;
        if ( this.currentIndex < 0 ) {
            this.currentIndex = this.players.length - 1;
        }
        
        return {
            done: ( this.currentIndex == ( this.startIterationIndex - 1 ) ),
            value: currentPlayer
        };
    }
}

export default GamePlayersIterator;
