import { Point } from './point';
import { IThemes } from './themes';

import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

export class Card
{
    static draw(
        cx: CanvasRenderingContext2D | null,
        card: CardDto,
        point: Point,
        width: number,
        height: number,
        angle: number,
        theme: IThemes,
        position: PlayerPosition,
        highLighted: boolean,
        debugCards: boolean
    ): void {
        if ( ! cx ) {
            return;
        }
        
        const { x, y } = point;
        const image = new Image( width, height );
        
        if ( position === PlayerPosition.south || debugCards ) {
            let imgSrc = `/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/${card.cardIndex}.png`;
            
            image.src = imgSrc;
        } else {
            image.src = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/back.png";
        }
        
        cx.save();
        cx.translate( x, y );
        cx.rotate( angle );
        
        cx.drawImage(
            image,
            0,
            0,
            width,
            height
        );
        
        if ( highLighted ) {
            cx.globalAlpha = .50;
            cx.fillStyle = "black";
            cx.fillRect( 0, 0, width, height );
        }
        
        cx.restore();
    }
    
    static drawInPile(
        cx: CanvasRenderingContext2D | null,
        card: CardDto,
        point: Point,
        width: number,
        height: number,
        angle: number,
        theme: IThemes
    ): void {
        if ( ! cx ) {
            return;
        }
        
        const { x, y } = point;
        const image = new Image( width, height );
        
        image.src = `/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/${card.cardIndex}.png`;
        
        cx.save();
        cx.translate( x, y );
        cx.rotate( angle );
        
        cx.drawImage(
            image,
            0,
            0,
            width,
            height
        );
        
        cx.restore();
    }
}
