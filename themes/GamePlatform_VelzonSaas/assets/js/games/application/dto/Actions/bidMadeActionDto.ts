import ActionDto from './actionDto';

import { BidDto } from '@vankosoft/game-platform';

interface BidMadeActionDto extends ActionDto {
    bid: BidDto;
}

export default BidMadeActionDto;