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
import CardGamePlayerDto from '_@/GamePlatform/Model/CardGame/playerDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';

import { CardGamePlayerArea } from '../../../models/card-game-player-area';
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

import { Helper } from '../../../utils/helper';

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
    
    @Input() public width: number = 600;
    @Input() public height: number = 400;
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
    
    playerAreas: CardGamePlayerArea[] = [];
    cardWidth: number = 69;
    cardHeight: number = 94;
    cardOffset: number = 10;
    
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
            changes['height']
        ) {
            this.recalculateGeometry();
        }
        this.initPlayerAreas();
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
            this.initPlayerAreas();
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
    
    getPlayerWidth( pPos: PlayerPosition ): number
    {
        switch( pPos ) {
            case PlayerPosition.north:
                return this.width / 2;
                break;
            case PlayerPosition.south:
                return this.width / 2;
                break;
            case PlayerPosition.east:
                return this.cardHeight + 20;
                break;
            case PlayerPosition.west:
                return this.cardHeight + 20;
                break;
            default:
                throw new Error( `Invalid Player Position ${pPos}` );
        }
    }
    
    getPlayerHeight( pPos: PlayerPosition ): number
    {
        switch( pPos ) {
            case PlayerPosition.north:
                return this.cardHeight + 20;
                break;
            case PlayerPosition.south:
                return this.cardHeight + 20;
                break;
            case PlayerPosition.east:
                return this.height / 2;
                break;
            case PlayerPosition.west:
                return this.height / 2;
                break;
            default:
                throw new Error( `Invalid Player Position ${pPos}` );
        }
    }
    
    initPlayerAreas(): void
    {
        if ( ! this.game ) {
            return;
        }
        
        console.log( 'Players', this.game.players );
        this.playerAreas = [];
        var pw, ph, playerArea;
        for ( let p = 0; p < this.game.players.length; p++ ) {
            pw = this.getPlayerWidth( this.game.players[p].playerPosition );
            ph = this.getPlayerHeight( this.game.players[p].playerPosition );
            
            switch( this.game.players[p].playerPosition ) {
                case PlayerPosition.north:
                    playerArea = new CardGamePlayerArea(
                        ( this.width - pw ) / 2,
                        10,
                        pw,
                        ph,
                        this.game.players[p].playerPosition
                    );
                    this.playerAreas.push( playerArea );
                    break;
                case PlayerPosition.south:
                    playerArea = new CardGamePlayerArea(
                        ( this.width - pw ) / 2,
                        this.height - ph - 10,
                        pw,
                        ph,
                        this.game.players[p].playerPosition
                    );
                    this.playerAreas.push( playerArea );
                    break;
                case PlayerPosition.east:
                    playerArea = new CardGamePlayerArea(
                        this.width - pw - 10,
                        ( this.height - ph ) / 2,
                        pw,
                        ph,
                        this.game.players[p].playerPosition
                    );
                    this.playerAreas.push( playerArea );
                    break;
                case PlayerPosition.west:
                    playerArea = new CardGamePlayerArea(
                        10,
                        ( this.height - ph ) / 2,
                        pw,
                        ph,
                        this.game.players[p].playerPosition
                    );
                    this.playerAreas.push( playerArea );
                    break;
                default:
                    throw new Error( `Invalid Player Position ${this.game.players[p].playerPosition}` );
            }
        }
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
            console.log( this.game );
            this.drawPlayers( cx );
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
    
    drawDeck( cx: CanvasRenderingContext2D ): void
    {
        const image = new Image( this.cardWidth, this.cardHeight );
        image.src = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/back.png";
        
        var cardX = this.width / 2 - image.width / 2;
        var cardY = this.height / 2 - image.height / 2;
        
        cx.drawImage(
            image,
            cardX,
            cardY,
            this.cardWidth,
            this.cardHeight
        );
        /*
        for ( let c = 0; c < this?.game?.deck.length; c++ ) {
            cardX -= 1;
            cardY -= 1;
            
            cx.drawImage(
                image,
                cardX,
                cardY,
                this.cardWidth,
                this.cardHeight
            );
        }
        */
    }
    
    drawPlayers( cx: CanvasRenderingContext2D ): void
    {
        if ( ! cx ) {
            return;
        }
        
        if ( ! this.game ) {
            return;
        }
        
        console.log( 'Player Areas', this.playerAreas );
        for ( let pa = 0; pa < this.playerAreas.length; pa++ ) {
            this.drawPlayerArea( cx, this.playerAreas[pa] );
            this.drawCards( cx, this.game.players[this.playerAreas[pa].playerPosition] );
        }
    }
    
    drawCards( cx: CanvasRenderingContext2D, playerDto: CardGamePlayerDto ): void
    {
        console.log( 'PlayerDto', playerDto );
        
        var card, pa, xOffset = 0, yOffset = 0;
        for ( let c = 0; c < playerDto.cards.length; c++ ) {
            pa = this.playerAreas.find( ( x ) => x.playerPosition === playerDto.playerPosition );
            if ( ! pa ) {
                break;
            }
            card = playerDto.cards[c];
            
            const image = new Image( this.cardWidth, this.cardHeight );
            if ( pa.playerPosition === PlayerPosition.south ) {
                let imgSrc = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/";
                imgSrc += `${Helper.cardType( playerDto.cards[c].Type ).toLowerCase()}`;
                imgSrc += `${Helper.cardSuit( playerDto.cards[c].Suit ).toLowerCase()}.png`;
                
                image.src = imgSrc;
            } else {
                image.src = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/back.png";
            }
            
            if ( pa.playerPosition === PlayerPosition.east || pa.playerPosition === PlayerPosition.west ) {
                if ( pa.playerPosition === PlayerPosition.west ) {
                    xOffset = pa.width - this.cardHeight;
                }
                
                const cardX = pa.x + pa.width - xOffset;
                const cardY = pa.y + ( ( c + 1 ) * this.cardOffset );
                
                cx.save();
                cx.translate( cardX, cardY );
                cx.rotate( Math.PI / 2 );
                
                cx.drawImage(
                    image,
                    0,
                    0,
                    this.cardWidth,
                    this.cardHeight
                );
                
                cx.restore();
            } else {
                if ( pa.playerPosition === PlayerPosition.south ) {
                    yOffset = pa.height - this.cardHeight;
                }
                
                const cardX = pa.x + ( ( c + 1 ) * this.cardOffset );
                const cardY = pa.y + yOffset;
                
                cx.drawImage(
                    image,
                    cardX,
                    cardY,
                    this.cardWidth,
                    this.cardHeight
                );
            }
        }
    }
    
    drawPlayerArea( cx: CanvasRenderingContext2D, playerArea: CardGamePlayerArea ): void
    {
        if ( ! this.game || ! this.game.players[playerArea.playerPosition] ) {
            return;
        }
        
        //console.log( 'Canvas Rendering Context', cx );
        playerArea.drawBorder( cx );
        
        var x: number, y: number, angle;
        switch( playerArea.playerPosition ) {
            case PlayerPosition.north:
                x = this.width / 2;
                y = playerArea.y + playerArea.height - 10;
                angle = 0;
                break;
            case PlayerPosition.south:
                x = this.width / 2;
                y = playerArea.y + 10;
                angle = Math.PI;
                break;
            case PlayerPosition.east:
                x = playerArea.x + 10;
                y = playerArea.y + playerArea.height / 2;
                angle = Math.PI / 2;
                break;
            case PlayerPosition.west:
                x = playerArea.x + playerArea.width - 10;
                y = playerArea.y + playerArea.height / 2;
                angle = -Math.PI / 2;
                break;
            default:
                throw new Error( `Invalid Player Position ${playerArea.playerPosition}` );
        }
        
        cx.save();
        cx.translate( x, y );
        cx.rotate( angle );
        
        cx.textAlign = "center";
        cx.fillText( this.game.players[playerArea.playerPosition].name, 0, 0 );
        
        cx.restore();
    }
}
