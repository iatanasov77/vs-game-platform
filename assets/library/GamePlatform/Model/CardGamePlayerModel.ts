interface ICardGamePlayer
{
    id: string;
    
    containerId: string;
    name: string;
    type: string;
        
    announce: null | string;
    
    getHand(): any;
    setHand( hand: any ): this;
    getAnnounce(): any;
    setAnnounce( announce: any ): this;
}

export default ICardGamePlayer;
