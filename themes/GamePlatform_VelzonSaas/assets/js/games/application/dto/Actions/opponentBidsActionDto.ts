import ActionDto from './actionDto';

import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';

interface OpponentBidsActionDto extends ActionDto {
    bid: BidDto;
}

export default OpponentBidsActionDto;
