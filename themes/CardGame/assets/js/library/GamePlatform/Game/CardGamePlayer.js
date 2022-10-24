class CardGamePlayer
{
    id;
    
    containerId;
    
    name;
    
    type;
    
    cardsId;
    
    hand;
    
    announce;
    
    constructor( id, containerId, playerName, playerType )
    {
        this.id             = id;
        this.containerId    = containerId;
        this.name           = playerName;
        this.type           = playerType;
        
        switch ( this.containerId ) {
            case 'LeftPlayer':
                this.cardsId    = 'lefthand';
                
                break;
            case 'TopPlayer':
                this.cardsId    = 'upperhand';
                
                break;
            case 'RightPlayer':
                this.cardsId    = 'righthand';
                
                break;
            case 'BottomPlayer':
                this.cardsId    = 'lowerhand';
                
                break;
        }
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
