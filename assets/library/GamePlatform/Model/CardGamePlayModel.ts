import IGamePlay from "./GamePlayModel";

interface ICardGamePlay extends IGamePlay
{
    id: any;
    deck: any;
    
    getHands(): any;
}

export default ICardGamePlay;
