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
import { Subscription } from 'rxjs';

// App State
import { AppStateService } from '../../../state/app-state.service';

// Core Interfaces
import GameState from '_@/GamePlatform/Model/Core/gameState';

// CardGame Interfaces
import CardGameDto from '_@/GamePlatform/Model/CardGame/gameDto';
import CardGamePlayerDto from '_@/GamePlatform/Model/CardGame/playerDto';
import PlayerPosition from '_@/GamePlatform/Model/CardGame/playerPosition';
import CardDto from '_@/GamePlatform/Model/CardGame/cardDto';
import BidDto from '_@/GamePlatform/Model/CardGame/bidDto';
import BidType from '_@/GamePlatform/Model/CardGame/bidType';

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

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

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
    @Input() playerCards: Array<CardDto[]> | null = [];
    @Input() playerBids: BidDto[] = [];
    @Input() myPosition: PlayerPosition | null = PlayerPosition.south;
    @Input() themeName: string | null = 'green';
    @Input() timeLeft: number | null = 0;
    @Input() lobbyButtonsVisible: boolean = false;
    
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
    cardOffset: number = 30;
    playerAreaPadding: number = 10;
    playerAreaHeightAddition: number = 40;
    
    bidTypes = [
        'bridge-belote.bid-type.pass',
        'bridge-belote.bid-type.clubs',
        'bridge-belote.bid-type.diamonds',
        'bridge-belote.bid-type.hearts',
        'bridge-belote.bid-type.spades',
        
        'bridge-belote.bid-type.no-trumps',
        'bridge-belote.bid-type.all-trumps',
        'bridge-belote.bid-type.double',
        'bridge-belote.bid-type.re-double'
    ];
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appState: AppStateService,
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
        
        this.requestDraw();
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
    
    get theme(): IThemes
    {
        if ( !! this._theme && this._theme.name === this.themeName ) {
            return this._theme;
        }
        
        this._theme = new GreenTheme();
        if ( this.themeName === 'dark' ) this._theme = new DarkTheme();
        if ( this.themeName === 'light' ) this._theme = new LightTheme();
        if ( this.themeName === 'blue' ) this._theme = new BlueTheme();
        if ( this.themeName === 'pink' ) this._theme = new PinkTheme();
        
        return this._theme as IThemes;
    }
    
    translate(): void
    {
        /*
        this.you = this.translateService.instant( 'gameboard.you' );
        this.white = this.translateService.instant( 'gameboard.white' );
        this.black = this.translateService.instant( 'gameboard.black' );
        this.left = this.translateService.instant( 'gameboard.left' );
        
        this.requestDraw();
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
        if ( this.canvas ) {
            const canvasEl: HTMLCanvasElement = this.canvas.nativeElement;
            canvasEl.width = this.width;
            canvasEl.height = this.height;
        }
        
        this.initPlayerAreas();
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
        
        if ( ! this.playerAreas.length ) {
            this.initPlayerAreas();
        }
        
        const canvasEl: HTMLCanvasElement = this.canvas.nativeElement;
        canvasEl.width = this.width;
        canvasEl.height = this.height;
        const cx = this.cx;
        this.drawDeck( cx );
        
        // console.log( this.game );
        if ( this.game && ! this.lobbyButtonsVisible ) {
            this.drawPlayers( cx );
            this.drawPlayerBids( cx );
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
        image.src = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/back.png";
        
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
        
        if ( ! this.playerCards ) {
            return;
        }
        
        //console.log( 'Player Areas', this.playerAreas );
        for ( let pa = 0; pa < this.playerAreas.length; pa++ ) {
            this.drawPlayerArea( this.playerAreas[pa] );
            this.drawCards( this.playerCards[this.playerAreas[pa].playerPosition], this.playerAreas[pa].playerPosition );
        }
    }
    
    drawPlayerBids( cx: CanvasRenderingContext2D ): void
    {
        if ( ! cx ) {
            return;
        }
        
        if ( ! this.game ) {
            return;
        }
        
        if ( ! this.playerBids ) {
            return;
        }
        
        for ( let pa = 0; pa < this.playerAreas.length; pa++ ) {
            if ( this.playerBids.hasOwnProperty( pa ) ) {
                //console.log( 'Player Bid', this.playerBids[pa] );
                //alert( 'Bid Player: ' + pa + ' Bid Type: ' + this.playerBids[pa].Type );
                this.drawPlayerBid( this.playerAreas[pa], this.playerBids[pa] );
            }
        }
    }
    
    drawCards( playerCards: CardDto[], playerPosition: number ): void
    {
        if ( ! this.cx ) {
            return;
        }
        
        var card, pa, cardX, cardY, angle, xOffset = 0, yOffset = 0;
        var cardsWidth = this.cardWidth + ( ( playerCards.length - 1 ) * this.cardOffset );
        for ( let c = 0; c < playerCards.length; c++ ) {
            pa = this.playerAreas.find( ( x ) => x.playerPosition === playerPosition );
            if ( ! pa ) {
                continue;
            }
            
            card = playerCards[c];
            const image = new Image( this.cardWidth, this.cardHeight );
            if ( pa.playerPosition === PlayerPosition.south || window.gamePlatformSettings.debugCardGamePlayerCards ) {
                let imgSrc = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/";
                imgSrc += `${Helper.cardType( card.Type ).toLowerCase()}`;
                imgSrc += `${Helper.cardSuit( card.Suit ).toLowerCase()}.png`;
                
                image.src = imgSrc;
            } else {
                image.src = "/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote/back.png";
            }
            
            if ( pa.playerPosition === PlayerPosition.east || pa.playerPosition === PlayerPosition.west ) {
                if ( pa.playerPosition === PlayerPosition.west ) {
                    xOffset = pa.width - this.cardHeight;
                }
                
                cardX = pa.x + pa.width - xOffset;
                cardY = pa.y + pa.height / 2 - ( cardsWidth / 2 ) + ( c * this.cardOffset );
                angle = Math.PI / 2;
            } else {
                if ( pa.playerPosition === PlayerPosition.south ) {
                    yOffset = pa.height - this.cardHeight;
                }
                
                cardX = pa.x + pa.width / 2 - ( cardsWidth / 2 ) + ( c * this.cardOffset );
                cardY = pa.y + yOffset;
                angle = 0;
            }
            
            this.cx.save();
            this.cx.translate( cardX, cardY );
            this.cx.rotate( angle );
            
            this.cx.drawImage(
                image,
                0,
                0,
                this.cardWidth,
                this.cardHeight
            );
            
            this.cx.restore();
        }
    }
    
    initPlayerAreas()
    {
        if ( ! this.game ) {
            return;
        }
        
        this.playerAreas = [];
        var pw, ph, playerArea;
        for ( let p = 0; p < this.game.players.length; p++ ) {
            switch( this.game.players[p].playerPosition ) {
                case PlayerPosition.north:
                    pw = this.width / 2;
                    ph = this.cardHeight + this.playerAreaHeightAddition;
                    
                    playerArea = new CardGamePlayerArea(
                        ( this.width - pw ) / 2,
                        this.playerAreaPadding,
                        pw,
                        ph,
                        this.game.players[p].playerPosition
                    );
                    this.playerAreas.push( playerArea );
                    
                    break;
                case PlayerPosition.south:
                    pw = this.width / 2;
                    ph = this.cardHeight + this.playerAreaHeightAddition;
                    
                    playerArea = new CardGamePlayerArea(
                        ( this.width - pw ) / 2,
                        this.height - ph - this.playerAreaPadding,
                        pw,
                        ph,
                        this.game.players[p].playerPosition
                    );
                    this.playerAreas.push( playerArea );
                    
                    break;
                case PlayerPosition.east:
                    pw = this.cardHeight + this.playerAreaHeightAddition;
                    ph = this.height / 1.3;
                    
                    playerArea = new CardGamePlayerArea(
                        this.width - pw - this.playerAreaPadding,
                        ( this.height - ph ) / 2,
                        pw,
                        ph,
                        this.game.players[p].playerPosition
                    );
                    this.playerAreas.push( playerArea );
                    
                    break;
                case PlayerPosition.west:
                    pw = this.cardHeight + this.playerAreaHeightAddition;
                    ph = this.height / 1.3;
                    
                    playerArea = new CardGamePlayerArea(
                        this.playerAreaPadding,
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
    
    drawPlayerArea( playerArea: CardGamePlayerArea ): void
    {
        if ( ! this.cx ) {
            return;
        }
        
        if ( ! this.game || ! this.game.players[playerArea.playerPosition] ) {
            return;
        }
        
        if ( window.gamePlatformSettings.debugCardGamePlayerAreas ) {
            playerArea.drawBorder( this.cx );
        }
        
        var x: number, y: number, angle;
        switch( playerArea.playerPosition ) {
            case PlayerPosition.north:
                x = this.width / 2;
                y = playerArea.y + playerArea.height - this.playerAreaHeightAddition / 2;
                angle = 0;
                break;
            case PlayerPosition.south:
                x = this.width / 2;
                y = playerArea.y + this.playerAreaHeightAddition / 2;
                angle = Math.PI;
                break;
            case PlayerPosition.east:
                x = playerArea.x + this.playerAreaHeightAddition / 2;
                y = playerArea.y + playerArea.height / 2;
                angle = Math.PI / 2;
                break;
            case PlayerPosition.west:
                x = playerArea.x + playerArea.width - this.playerAreaHeightAddition / 2;
                y = playerArea.y + playerArea.height / 2;
                angle = -Math.PI / 2;
                break;
            default:
                throw new Error( `Invalid Player Position ${playerArea.playerPosition}` );
        }
        
        this.cx.save();
        this.cx.translate( x, y );
        this.cx.rotate( angle );
        
        this.cx.textAlign = "center";
        this.cx.font = "bold 10pt Courier";
        this.cx.fillText( this.game.players[playerArea.playerPosition].name, 0, 0 );
        
        this.cx.restore();
    }
    
    drawPlayerBid( playerArea: CardGamePlayerArea, bid: BidDto ): void
    {
        if ( ! this.cx ) {
            return;
        }
        
        var x: number, y: number, angle;
        switch( playerArea.playerPosition ) {
            case PlayerPosition.north:
                x = this.width / 2;
                y = playerArea.y + playerArea.height;
                angle = 0;
                break;
            case PlayerPosition.south:
                x = this.width / 2;
                y = playerArea.y;
                angle = Math.PI;
                break;
            case PlayerPosition.east:
                x = playerArea.x;
                y = playerArea.y + playerArea.height / 2;
                angle = Math.PI / 2;
                break;
            case PlayerPosition.west:
                x = playerArea.x + playerArea.width;
                y = playerArea.y + playerArea.height / 2;
                angle = -Math.PI / 2;
                break;
            default:
                throw new Error( `Invalid Player Position ${playerArea.playerPosition}` );
        }
        
        this.cx.save();
        this.cx.translate( x, y );
        this.cx.rotate( angle );
        
        this.cx.textAlign = "center";
        this.cx.font = "bold 10pt Courier";
        this.cx.fillText( this.translateService.instant( this.bidTypes[bid.Type] ), 0, 0 );
        
        this.cx.restore();
    }
}
