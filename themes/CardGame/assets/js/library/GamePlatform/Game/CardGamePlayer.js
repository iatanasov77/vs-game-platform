class CardGamePlayer
{
    type;
    
    hand;
    
    announce;
    
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
    
    getAnnounce()
    {
        return this.announce;
    }
    
    setAnnounce( announce )
    {
        this.announce   = announce;
        
        return this;
    }
}

export default CardGamePlayer;
