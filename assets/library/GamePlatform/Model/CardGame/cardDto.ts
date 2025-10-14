import CardSuit from './cardSuit';
import CardType from './cardType';
import PlayerPosition from './playerPosition';

interface CardDto
{
    Suit: CardSuit;
    Type: CardType;
    
    position: PlayerPosition;
    cardIndex: string;
    animate: boolean;
}

export default CardDto;
