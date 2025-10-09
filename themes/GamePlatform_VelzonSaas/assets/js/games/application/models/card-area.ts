export class CardArea
{
    constructor(
        public x: number,
        public y: number,
        public width: number,
        public height: number,
        public cardIdx: string
    ) {}
    
    hasValidCard = false;
    canBePlayed = false;
    
    set(
        x: number,
        y: number,
        width: number,
        height: number,
        cardIdx: string
    ): void {
        this.x = x;
        this.y = y;
        this.width = width;
        this.height = height;
        this.cardIdx = cardIdx;
    }
    
    public contains( x: number, y: number ): boolean
    {
        return (
            x >= this.x &&
            x <= this.x + this.width &&
            y >= this.y &&
            y <= this.y + this.height
        );
    }
    
    drawBorder( cx: CanvasRenderingContext2D ): void
    {
        cx.strokeRect( this.x, this.y, this.width, this.height );
    }
}
