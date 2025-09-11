import {
    Component,
    Inject,
    EventEmitter,
    HostListener,
    OnInit,
    AfterViewInit,
    OnDestroy,
    ViewChild,
    ElementRef,
    Input,
    OnChanges,
    SimpleChanges
} from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Actions, ofType } from '@ngrx/effects';
import { Observable, of } from 'rxjs';

// App State
import { AppStateService } from '../../../state/app-state.service';

import {
    selectGameRoomSuccess
} from '../../../+store/game.actions';
import { GameState } from '../../../+store/game.reducers';

// CardGame Interfaces
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

import {
    Point,
    MoveAnimation
} from '../../../models/';

import {
    BlueTheme,
    DarkTheme,
    GreenTheme,
    IThemes,
    LightTheme,
    PinkTheme
} from '../../../models/themes';

import templateString from './bridge-belote-board.component.html'
import styleString from './bridge-belote-board.component.scss'

@Component({
    selector: 'bridge-belote-board',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class BridgeBeloteBoardComponent implements AfterViewInit, OnChanges
{
    @ViewChild( 'canvas' ) public canvas: ElementRef | undefined;
    
    @Input() public width = 600;
    @Input() public height = 400;
    @Input() game: CardGameDto | null = null;
    @Input() myPosition: PlayerPosition | null = PlayerPosition.south;
    @Input() themeName: string | null = 'green';
    @Input() timeLeft: number | null = 0;
    @Input() lobbyButtonsVisible: boolean = false;
    
    gameState?: GameState;
    
    cx: CanvasRenderingContext2D | null = null;
//     dragging: CheckerDrag | null = null;
//     cursor: Point = new Point( 0, 0 );
    framerate = 60;
    animatedMove: MoveAnimation | undefined = undefined;
//     animationSubscription: Subscription;
//     lastTouch: Point | undefined = undefined;
    hasTouch = false;
    whitesName = '';
    blacksName = '';
    
    //theme: IThemes = new DarkTheme();
    private _theme: IThemes | undefined = undefined;
    
    cardWidth: number = 69;
    cardHeight: number = 94;
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appState: AppStateService,
        @Inject( Actions ) private actions$: Actions,
    ) {
        
    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        if (
            changes['width'] ||
            changes['height'] ||
            changes['flipped'] ||
            changes['rotated']
        ) {
            this.recalculateGeometry();
        }
        this.requestDraw();
        
        /*
        const bName = this.myColor === PlayerColor.black
            ? this.you
            : this.game?.blackPlayer.name;
        const wName = this.myColor === PlayerColor.white
            ? this.you
            : this.game?.whitePlayer.name;
        
        const bLeft = this.game?.blackPlayer.pointsLeft;
        const wLeft = this.game?.whitePlayer.pointsLeft;
        
        this.blacksName = this.game ? `${bName} - ${bLeft} ${this.left}` : '';
        this.whitesName = this.game ? `${wName} - ${wLeft} ${this.left}` : '';
        */
        // console.log( this.game?.playState );
    }
    
    ngAfterViewInit(): void
    {
        if ( ! this.canvas ) {
            return;
        }
        
        const canvasEl: HTMLCanvasElement = this.canvas.nativeElement;
        this.cx = canvasEl.getContext( '2d' );
        if ( this.cx ) this.cx.imageSmoothingEnabled = true;
        
        // I. Atanasov - Get Translations Before Draw
        this.translateService.getTranslation( this.translateService.getBrowserLang()! ).subscribe( () => {
            this.translate();
            this.requestDraw();
        });
        
        this.translateService.onLangChange.subscribe( () => {
            this.translate();
        });
    }
    
    translate(): void
    {
        /*
        this.you = this.translateService.instant( 'gameboard.you' );
        this.white = this.translateService.instant( 'gameboard.white' );
        this.black = this.translateService.instant( 'gameboard.black' );
        this.left = this.translateService.instant( 'gameboard.left' );
        */
        this.requestDraw();
    }
    
    onMouseMove( event: MouseEvent ): void
    {
        
    }
    
    onMouseDown( event: MouseEvent ): void
    {
        
    }
    
    onMouseUp( event: MouseEvent ): void
    {
        
    }
    
    @HostListener( 'window:orientationchange', ['$event'] )
    onOrientationChange(): void
    {
        this.recalculateGeometry();
        console.log( 'orient change' );
    }
    
    recalculateGeometry(): void
    {
    
    }
    
    requestDraw(): void
    {
        requestAnimationFrame( this.draw.bind( this ) );
    }
    
    draw(): number
    {
        if ( ! this.canvas || ! this.cx ) {
            return 0;
        }
        
        const canvasEl: HTMLCanvasElement = this.canvas.nativeElement;
        canvasEl.width = this.width;
        canvasEl.height = this.height;
        const cx = this.cx;
        this.drawDeck( cx );
        
        if ( this.game && ! this.lobbyButtonsVisible ) {
            this.drawPlayers( cx );
            this.drawCards( cx );
        }
        
        if ( this.animatedMove ) {
            this.animatedMove.draw( cx, this.cardWidth );
        }
        
        // *** NOT PROD CODE
        // this.drawIcon(cx);
        // this.drawDebugRects(cx);
        // *** NOT PROD CODE
        return 0;
    }
    
    drawDeck( cx: CanvasRenderingContext2D )
    {
        const image = new Image( this.cardWidth, this.cardHeight );
        image.src = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/back.png";
        
        cx.drawImage(
            image,
            this.width / 2 - image.width / 2,
            this.height / 2 - image.height / 2,
            this.cardWidth,
            this.cardHeight
        );
    }
    
    drawPlayers( cx: CanvasRenderingContext2D )
    {
    
    }
    
    drawCards( cx: CanvasRenderingContext2D )
    {
        console.log( this.game );
        //alert( this.game.players.length );
//         for ( i = 0; i < lowerhand.length; i++ ) {
//             lowerhand[i].el.css( 'left', 10 + ( i * 20 ) + 'px' );
//             lowerhand[i].el.css( 'top', '45px' );
// 
//             lowerhand[i].el.moveTo( '#lowerhand' );   // https://stackoverflow.com/questions/2596833/how-to-move-child-element-from-one-parent-to-another-using-jquery
//             lowerhand[i].el.css( 'z-index', '0' );
//         }
    }
}
