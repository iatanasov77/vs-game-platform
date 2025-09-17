import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

export class CardGamePlayerArea
{
    constructor(
        public x: number,
        public y: number,
        public width: number,
        public height: number,
        public playerPosition: PlayerPosition
    ) {}
    
    set(
        x: number,
        y: number,
        width: number,
        height: number,
        playerPosition: PlayerPosition
    ): void {
        this.x = x;
        this.y = y;
        this.width = width;
        this.height = height;
        this.playerPosition = playerPosition;
    }
    
    drawBorder( cx: CanvasRenderingContext2D ): void
    {
        cx.strokeRect( this.x, this.y, this.width, this.height );
    }
}
