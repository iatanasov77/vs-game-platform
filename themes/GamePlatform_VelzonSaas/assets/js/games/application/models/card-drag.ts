import { CardArea } from './';
import { PlayerPosition } from '@vankosoft/game-platform';

/**
 * May be will be used if I Create Solitaires ( Пасианси )
 */
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
