import IGamePlay from "./GamePlayModel";

interface ICardGamePlay extends IGamePlay
{
    deck: any;
    
    getHands(): any;
}

export default ICardGamePlay;
