import { Point } from './point';
import { IThemes } from './themes';

import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';

export class Checker
{
    static draw(
        cx: CanvasRenderingContext2D | null,
        point: Point,
        width: number,
        theme: IThemes,
        color: PlayerColor,
        highLighted: boolean,
        shaddow: boolean,
        flipped: boolean,
        hint: boolean
    ): void {
        if ( ! cx ) {
            return;
        }
        const { x, y } = point;
        if ( shaddow ) {
            const off = flipped ? -8 : 8;
            cx.shadowColor = 'rgba(0, 0, 0, 0.4)';
            cx.shadowBlur = 8;
            cx.shadowOffsetX = off;
            cx.shadowOffsetY = off;
        }
        
        cx.strokeStyle = theme.checkerBorder;
        if ( color === PlayerColor.black ) {
            cx.fillStyle = theme.blackChecker;
        } else {
            cx.fillStyle = theme.whiteChecker;
        }
        cx.beginPath();
        cx.ellipse( x, y, width, width, 0, 0, 2 * Math.PI );
        cx.closePath();
        if ( hint ) {
            cx.globalAlpha = 0.5;
        }
        cx.fill();
        cx.globalAlpha = 1;
        
        if ( highLighted ) {
            cx.fillStyle = 'rgba(40, 221, 46, 0.3)'; // todo: get from theme
            cx.fill();
        }
        
        // clossy checker
        const glossyW = width * 0.9;
        if ( ! flipped ) {
            const glossy = cx.createLinearGradient( x, y - width / 2, x, y );
            glossy.addColorStop( 0, 'rgba(255, 255, 255, 0.25)' );
            glossy.addColorStop( 1, 'rgba(255, 255, 255, 0.15' );
            cx.fillStyle = glossy;
            cx.beginPath();
            cx.ellipse( x, y, glossyW, glossyW, 0, Math.PI, 2 * Math.PI );
            cx.closePath();
            cx.fill();
        } else {
            const glossy = cx.createLinearGradient( x, y, x, y + width / 2 );
            glossy.addColorStop( 0, 'rgba(255, 255, 255, 0.15)' );
            glossy.addColorStop( 1, 'rgba(255, 255, 255, 0.25' );
            cx.fillStyle = glossy;
            cx.beginPath();
            cx.ellipse( x, y, glossyW, glossyW, 0, 2 * Math.PI, Math.PI );
            cx.closePath();
            cx.fill();
        }
    }
}
