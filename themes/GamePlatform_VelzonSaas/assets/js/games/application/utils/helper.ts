import { CardSuit } from '@vankosoft/game-platform';
import { BridgeBeloteCardType } from '@vankosoft/game-platform';
import { ContractBridgeCardType } from '@vankosoft/game-platform';

export class Helper
{
    public static delay( ms: number )
    {
        return new Promise( ( resolve ) => setTimeout( resolve, ms ) );
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
    
    public static bridgeBeloteCardType( type: BridgeBeloteCardType ): string
    {
        switch( type ) {
            case BridgeBeloteCardType.Seven:
                return 'Seven';
                break;
            case BridgeBeloteCardType.Eight:
                return 'Eight';
                break;
            case BridgeBeloteCardType.Nine:
                return 'Nine';
                break;
            case BridgeBeloteCardType.Ten:
                return 'Ten';
                break;
            case BridgeBeloteCardType.Jack:
                return 'Jack';
                break;
            case BridgeBeloteCardType.Queen:
                return 'Queen';
                break;
            case BridgeBeloteCardType.King:
                return 'King';
                break;
            case BridgeBeloteCardType.Ace:
                return 'Ace';
                break;
            default:
                throw new Error( `Invalid Card Type ${type}` );
        }
    }
    
    public static contractBridgeCardType( type: ContractBridgeCardType ): string
    {
        switch( type ) {
            case ContractBridgeCardType.Two:
                return 'Two';
                break;
            case ContractBridgeCardType.Three:
                return 'Three';
                break;
            case ContractBridgeCardType.Four:
                return 'Four';
                break;
            case ContractBridgeCardType.Five:
                return 'Five';
                break;
            case ContractBridgeCardType.Six:
                return 'Six';
                break;
            case ContractBridgeCardType.Seven:
                return 'Seven';
                break;
            case ContractBridgeCardType.Eight:
                return 'Eight';
                break;
            case ContractBridgeCardType.Nine:
                return 'Nine';
                break;
            case ContractBridgeCardType.Ten:
                return 'Ten';
                break;
            case ContractBridgeCardType.Jack:
                return 'Jack';
                break;
            case ContractBridgeCardType.Queen:
                return 'Queen';
                break;
            case ContractBridgeCardType.King:
                return 'King';
                break;
            case ContractBridgeCardType.Ace:
                return 'Ace';
                break;
            default:
                throw new Error( `Invalid Card Type ${type}` );
        }
    }
}
