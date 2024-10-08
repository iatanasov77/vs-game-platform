import ICardGamePlayer from '../Model/CardGamePlayerModel';

class CardGamePlayer implements ICardGamePlayer
{
    id: any;
    
    containerId: any;
    
    name: any;
    
    type: any;
    
    currentDealer: any = false;
    
    cardsId: any;
    
    hand: any;
    
    announce: any;
    
    constructor( id: any, containerId: any, playerName: any, playerType: any, currentDealer: boolean = false )
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
    
    getHand(): any
    {
        return this.hand;
    }
    
    setHand( hand: any ): this
    {
        this.hand   = hand;
        
        return this;
    }
    
    getAnnounce(): any
    {
        return this.announce;
    }
    
    setAnnounce( announce: any ): this
    {
        this.announce   = announce;
        
        return this;
    }
}

export default CardGamePlayer;
