import ActionDto from './actionDto';

import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import BidType from '_@/GamePlatform/Model/CardGame/bidType';

interface BidActionDto extends ActionDto {
    Player: PlayerPosition;
    Type: BidType;
}

export default BidActionDto;