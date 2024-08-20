import GameInterface from "./GameInterface";

interface ICardGame extends GameInterface
{
    //deck: any;
    
    getHands(): any;
}

export default ICardGame;
