import CardSuit from '_@/GamePlatform/Model/CardGame/cardSuit';
import CardType from '_@/GamePlatform/Model/CardGame/cardType';

export class Helper
{
    public static delay( ms: number )
    {
        return new Promise( ( resolve ) => setTimeout( resolve, ms ) );
    }
    
    public static cardType( type: CardType ): string
    {
        switch( type ) {
            case CardType.Seven:
                return 'Seven';
                break;
            case CardType.Eight:
                return 'Eight';
                break;
            case CardType.Nine:
                return 'Nine';
                break;
            case CardType.Ten:
                return 'Ten';
                break;
            case CardType.Jack:
                return 'Jack';
                break;
            case CardType.Queen:
                return 'Queen';
                break;
            case CardType.King:
                return 'King';
                break;
            case CardType.Ace:
                return 'Ace';
                break;
            default:
                throw new Error( `Invalid Card Type ${type}` );
        }
    }
    
    public static cardSuit( suit: CardSuit ): string
    {
        switch( suit ) {
            case CardSuit.Club:
                return 'Club';
                break;
            case CardSuit.Diamond:
                return 'Diamond';
                break;
            case CardSuit.Heart:
                return 'Heart';
                break;
            case CardSuit.Spade:
                return 'Spade';
                break;
            default:
                throw new Error( `Invalid Card Suit ${suit}` );
        }
    }
}
