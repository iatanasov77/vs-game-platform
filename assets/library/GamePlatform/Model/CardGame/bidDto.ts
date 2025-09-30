import PlayerPosition from './playerPosition';
import BidType from './bidType';

interface BidDto
{
    Player: PlayerPosition;
    Type: BidType;
    NextBids: BidDto[];
}

export default BidDto;
