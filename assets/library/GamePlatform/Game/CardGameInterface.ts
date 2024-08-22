import GameInterface from "./GameInterface";

interface ICardGame extends GameInterface
{
    id: any;
    deck: any;
    
    getHands(): any;
}

export default ICardGame;
