import IGamePlayer from './GamePlayerModel'

interface ICardGamePlayer extends IGamePlayer
{
    announce: null | string;
    
    getHand(): any;
    setHand( hand: any ): this;
    getAnnounce(): any;
    setAnnounce( announce: any ): this;
}

export default ICardGamePlayer;
