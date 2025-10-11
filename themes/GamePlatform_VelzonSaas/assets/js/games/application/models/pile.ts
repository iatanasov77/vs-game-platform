import { IThemes } from './themes';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';

export class Pile
{
    static drawAsPile(
        cx: CanvasRenderingContext2D | null,
        pile: CardDto[],
        boardWidth: number,
        boardHeight: number,
        cardWidth: number,
        cardHeight: number,
        theme: IThemes
    ): void {
        if ( ! cx ) {
            return;
        }
        
        var card, cardX, cardY, angle, image;
        var pileOffset = 3;
        for ( let c = 0; c < pile.length; c++ ) {
            cardX = boardWidth / 2 - ( pile.length - c ) * cardWidth / Math.PI;
            
            if ( c == 0 || c == 3 ) {
                cardY = boardHeight / 2 - cardHeight / 2 + 25;
            } else {
                cardY = boardHeight / 2 - cardHeight / 2 + 5;
            }
            
            if ( c < 2 ) {
                angle = - ( Math.PI / 4 );
            } else {
                angle = Math.PI / 4;
            }
            
            image = new Image( cardWidth, cardHeight );
            image.src = `/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/${pile[c].cardIndex}.png`;
            
            cx.save();
            cx.translate( cardX, cardY );
            cx.rotate( angle );
            
            cx.drawImage(
                image,
                0,
                0,
                cardWidth,
                cardHeight
            );
            
            cx.restore();
            
            pileOffset--;
        }
    }
    
    static drawAsRound(
        cx: CanvasRenderingContext2D | null,
        pile: CardDto[],
        boardWidth: number,
        boardHeight: number,
        cardWidth: number,
        cardHeight: number,
        theme: IThemes
    ): void {
        if ( ! cx ) {
            return;
        }
        
        var card, cardX, cardY, angle, image;
        var pileOffset = 3;
        for ( let c = 0; c < pile.length; c++ ) {
            cardX = boardWidth / 2 - ( ( pile.length - c ) * cardWidth + pileOffset * 20 ) / 2;
            
            if ( c == 0 || c == 3 ) {
                cardY = boardHeight / 2 - cardHeight / 2 + 40;
            } else {
                cardY = boardHeight / 2 - cardHeight / 2 + 20;
            }
            
            if ( c < 2 ) {
                angle = - ( Math.PI / 4 );
            } else {
                angle = Math.PI / 4;
            }
            
            image = new Image( cardWidth, cardHeight );
            image.src = `/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/${pile[c].cardIndex}.png`;
            
            cx.save();
            cx.translate( cardX, cardY );
            cx.rotate( angle );
            
            cx.drawImage(
                image,
                0,
                0,
                cardWidth,
                cardHeight
            );
            
            cx.restore();
            
            pileOffset--;
        }
    }
}
