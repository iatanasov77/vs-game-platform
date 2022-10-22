class CardGamePlayer
{
    type;
    
    hand;
    
    constructor( playerType )
    {
        this.type   = playerType;
    }
    
    getHand()
    {
        return this.hand;
    }
    
    setHand( hand )
    {
        this.hand   = hand;
        
        return this;
    }
}

export default CardGamePlayer;
