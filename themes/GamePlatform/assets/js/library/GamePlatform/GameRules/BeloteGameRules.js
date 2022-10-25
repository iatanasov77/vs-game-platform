import AbstractGameRules from './AbstractGameRules';
import Announce from '../CardGameAnnounce/Announce';

class BeloteGameRules extends AbstractGameRules
{
    rules( announce )
    {
        switch ( announce ) {
            case Announce.PASS:
                return Announce.PASS;
                
                break;
            case Announce.CLOVER:
                return Announce.CLOVER;
                
                break;
            case Announce.DIAMOND:
                return Announce.DIAMOND;
                
                break;
            case Announce.HEART:
                return Announce.HEART;
                
                break;
            case Announce.SPADE:
                return Announce.SPADE;
                
                break;
            case Announce.BEZ_KOZ:
                return Announce.BEZ_KOZ;
                
                break;
            case Announce.VSICHKO_KOZ:
                return Announce.VSICHKO_KOZ;
                
                break;
            default:
                throw new Error( "Wrong Announce !!!" );
        }
        // alert( 'BeloteGameRules' );
    }
}

export default BeloteGameRules;
