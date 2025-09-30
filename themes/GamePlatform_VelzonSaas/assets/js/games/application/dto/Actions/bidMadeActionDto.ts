import ActionDto from './actionDto';

import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';

interface BidMadeActionDto extends ActionDto {
    bid: BidDto;
}

export default BidMadeActionDto;