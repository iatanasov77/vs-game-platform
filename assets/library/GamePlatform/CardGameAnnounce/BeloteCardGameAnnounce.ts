import AbstractCardGameAnnounce from './AbstractCardGameAnnounce';
import Announce from './Announce';

class BeloteCardGameAnnounce extends AbstractCardGameAnnounce
{
    public override announce( hand: any, lastAnnounce: any )
    {
        switch ( lastAnnounce ) {
            case undefined:
                return Announce.VSICHKO_KOZ;
                
                break;
            default:
                return Announce.PASS;
        }
    }
    
    public override getAnnounce( announces: any )
    {
        if ( ! Array.isArray( announces ) ) {
            throw new Error( "The parameter 'announces' should be an Array object !!!" );
        }
        
        return announces[0];
        return announces[announces.length - 1];
    }
}

export default BeloteCardGameAnnounce;
