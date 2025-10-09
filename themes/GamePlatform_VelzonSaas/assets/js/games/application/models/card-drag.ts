import { CardArea } from './';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

export class CardDrag
{
    constructor(
        public cardArea: CardArea,
        public xDown: number,
        public yDown: number,
        public cardIdx: string,
        public position: PlayerPosition
    ) {}
}
