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
    SimpleChanges,
    Output
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
import AnnounceDto from '_@/GamePlatform/Model/CardGame/announceDto';
import AnnounceType from '_@/GamePlatform/Model/CardGame/announceType';

import { CardGamePlayerArea } from '../../../models/card-game-player-area';
import { Card, CardArea, CardDrag, Point, MoveAnimation, Pile } from '../../../models/';
import {
    BlueTheme,
    DarkTheme,
    GreenTheme,
    IThemes,
    LightTheme,
    PinkTheme,
    CardGameTheme
} from '../../../models/themes';

import { GameVariant } from '../../../game.variant';

import templateString from './card-game-board.component.html'
import styleString from './card-game-board.component.scss'

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Component({
    selector: 'card-game-board',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class CardGameBoardComponent implements AfterViewInit, OnChanges
{
    @ViewChild( 'canvas' ) public canvas: ElementRef | undefined;
    
    @Input() public width: number = 710;
    @Input() public height: number = 510;
    @Input() game: CardGameDto | null = null;
    @Input() playerCards: Array<CardDto[]> | null = [];
    @Input() playerBids: BidDto[] = [];
    @Input() playerAnnounces: Array<AnnounceDto[]> | null = [];
    @Input() deck: CardDto[] = [];
    @Input() pile: CardDto[] = [];
    @Input() myPosition: PlayerPosition | null = PlayerPosition.south;
    @Input() themeName: string | null = 'card-game';
    @Input() timeLeft: number | null = 0;
    @Input() lobbyButtonsVisible: boolean = false;
    
    @Output() playCard = new EventEmitter<CardDto>();
    @Output() playCardAnimFinished = new EventEmitter<void>();
    
    borderWidth = 0;
    cx: CanvasRenderingContext2D | null = null;
    dragging: CardDrag | null = null; // May be will be used if I Create Solitaires ( Пасианси )
    cursor: Point = new Point( 0, 0 );
    cxCursor: string = 'default';
    framerate = 60;
    animatedMove: MoveAnimation | undefined = undefined;
//     animationSubscription: Subscription;
    lastTouch: Point | undefined = undefined;
    hasTouch = false;
    whitesName = '';
    blacksName = '';
    
    //theme: IThemes = new DarkTheme();
    private _theme: IThemes | undefined = undefined;
    
    playerAreas: CardGamePlayerArea[] = [];
    cardAreas: CardArea[] = [];
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
    
    announceTypes = [
        'bridge-belote.announce-type.belot',
        'bridge-belote.announce-type.sequence-of-3',
        'bridge-belote.announce-type.sequence-of-4',
        'bridge-belote.announce-type.sequence-of-5',
        'bridge-belote.announce-type.sequence-of-6',
        'bridge-belote.announce-type.sequence-of-7',
        'bridge-belote.announce-type.sequence-of-8',
        'bridge-belote.announce-type.four-of-a-kind',
        'bridge-belote.announce-type.four-nines',
        'bridge-belote.announce-type.four-jacks'
    ];
    
    constructor(
        @Inject( TranslateService ) private translateService: TranslateService,
        @Inject( AppStateService ) private appState: AppStateService,
    ) {
        for ( let r = 0; r < 8; r++ ) {
            this.cardAreas.push( new CardArea( 0, 0, 0, 0, '' ) );
        }
    }
    
    ngOnChanges( changes: SimpleChanges ): void
    {
        if (
            changes['width'] ||
            changes['height']
        ) {
            this.recalculateGeometry();
        }
        
        if ( changes['playerCards'] ) {
            this.initCardAreas();
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
        
        this._theme = new CardGameTheme();
        if ( this.themeName === 'dark' ) this._theme = new DarkTheme();
        if ( this.themeName === 'light' ) this._theme = new LightTheme();
        if ( this.themeName === 'blue' ) this._theme = new BlueTheme();
        if ( this.themeName === 'pink' ) this._theme = new PinkTheme();
        if ( this.themeName === 'green' ) this._theme = new GreenTheme();
        
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
    
    canPlayCard(): boolean
    {
        if ( ! this.game ) {
            return false;
        }
        
        if ( this.myPosition != this.game.currentPlayer ) {
            return false;
        }
        
        if ( this.game.playState === GameState.ended ) {
            return false;
        }
        
        return true;
    }
    
    onMouseMove( event: MouseEvent ): void
    {
        // console.log( 'mousemove', event );
        const point = this.getPoint( event );
        this.handleMove( point.x, point.y );
    }
    
    onMouseDown( event: MouseEvent ): void
    {
        // console.log( 'mouse down', event );
        if ( this.hasTouch ) {
            return;
        }
        const point = this.getPoint( event );
        
        this.handleDown( point.x, point.y );
    }
    
    onMouseUp( event: MouseEvent ): void
    {
        // console.log( 'mouse up', event );
        if ( this.hasTouch ) {
            return;
            // on mobile there is a mouse up event if the mouse hasn't been moved.
        }
        const point = this.getPoint( event );
        this.handleUp( point.x, point.y );
    }
    
    getPoint( event: MouseEvent ): Point
    {
        // Cool that offsets are also rotated. Is that true on all browsers?
        return { x: event.offsetX, y: event.offsetY };
    }
    
    getTouchPoint( touch: any ): Point
    {
        const parent = ( this.canvas?.nativeElement as HTMLElement )?.offsetParent as HTMLElement;
        
        const eventX = touch.pageX || touch.originalEvent?.pageX;
        const eventY = touch.pageY || touch.originalEvent?.pageY;
        
        return {
            x: eventX - parent.offsetLeft,
            y: eventY - parent.offsetTop - 20
        };
    }
    
    handleMove( clientX: number, clientY: number ): void
    {
        this.cursor.x = clientX;
        this.cursor.y = clientY;
        
        if ( ! this.game ) {
            return;
        }
        
        if ( ! this.canPlayCard() ) {
            return;
        }
        
        if ( this.dragging ) {
            this.requestDraw();
            return;
        }
        
        this.setCanBePlayed( clientX, clientY );
    }
    
    setCanBePlayed( clientX: number, clientY: number ): void
    {
        if ( ! this.game ) {
            return;
        }
        //console.log( 'Valid Cards', this.game.validCards );
        
        // resetting all
        this.cardAreas.forEach( ( rect ) => {
            rect.hasValidCard = false;
            rect.canBePlayed = false;
        });
        
        this.cxCursor = 'default';
        for ( let i = 0; i < this.cardAreas.length; i++ ) {
            const rect = this.cardAreas[i];
            if ( ! rect.contains( clientX, clientY ) ) {
                continue;
            }
            
            const cards = this.game.validCards.filter( ( c: CardDto ) => c.cardIndex === rect.cardIdx );
            if ( cards.length > 0 ) {
                rect.hasValidCard = true;
                
                var index = 0;
                cards.forEach( ( card: CardDto ) => {
                    const cardIdx = index++;
                    const area = this.cardAreas.find( ( r ) => r.cardIdx === rect.cardIdx );
                    
                    if ( area ) {
                        area.canBePlayed = true;
                        this.cxCursor = 'pointer';
                        //alert( area.canBePlayed );
                    }
                });
            }
        }
        //console.log( 'Card Areas', this.cardAreas );
        
        this.requestDraw();
    }
    
    handleDown( clientX: number, clientY: number ): void
    {
        if ( ! this.game ) {
            return;
        }
        
        if ( ! this.canPlayCard() ) {
            return;
        }
        
        for ( let i = 0; i < this.cardAreas.length; i++ ) {
            const rect = this.cardAreas[i];
            if ( ! rect.contains( clientX, clientY ) ) {
                continue;
            }
            let ptIdx = rect.cardIdx;
            // alert( ptIdx );
            
            // The moves are ordered  by backend by dice value.
            const card = this.game.validCards.find( ( c: CardDto ) => c.cardIndex === ptIdx );
            if ( card !== undefined ) {
                this.dragging = new CardDrag(
                    rect,
                    clientX,
                    clientY,
                    ptIdx,
                    card.position
                );
                // console.log( 'dragging', this.dragging );
                break;
            }
        }
    }
    
    handleUp( clientX: number, clientY: number ): void
    {
        if ( ! this.game) {
            return;
        }

        if ( ! this.canPlayCard() ) {
            return;
        }
        
        if ( ! this.dragging ) {
            return;
        }
        
        const { xDown, yDown, cardIdx } = this.dragging;
        
        // Unless the cursor has moved to far, this is a click event, and should move the move of the largest dice.
        const isClick = Math.abs( clientX - xDown ) < 3 && Math.abs( clientY - yDown ) < 3;
        
        const allRects: CardArea[] = [...this.cardAreas];
        
        for ( let i = 0; i < allRects.length; i++ ) {
            const rect = allRects[i];
            const x = clientX;
            const y = clientY;
            if ( ! rect.contains( x, y ) ) {
                continue;
            }
            let ptIdx = rect.cardIdx;
            let card: CardDto | undefined = undefined;
            if ( isClick ) {
                card = this.game.validCards.find( ( c: CardDto ) => c.cardIndex === ptIdx );
            } else {
                // Here is the same as above, but if i made dragging of later here will be different
                card = this.game.validCards.find( ( c: CardDto ) => c.cardIndex === ptIdx );
            }
            
            if ( card ) {
                this.playCard.emit( { ...card, animate: isClick } );
                break;
            }
        }
        this.requestDraw();
        
        // console.log( 'dragging null' );
        this.dragging = null;
    }
    
    @HostListener( 'window:orientationchange', ['$event'] )
    onOrientationChange(): void
    {
        this.recalculateGeometry();
        //console.log( 'orient change' );
    }
    
    recalculateGeometry(): void
    {
        if ( this.canvas ) {
            const canvasEl: HTMLCanvasElement = this.canvas.nativeElement;
            canvasEl.width = this.width;
            canvasEl.height = this.height;
        }
        
        this.borderWidth = this.width * 0.01;
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
        this.drawBoard( cx );
        
        // console.log( this.game );
        if ( this.game && this.game.playState !== GameState.ended && ! this.lobbyButtonsVisible ) {
            this.drawDeck( cx );
            this.drawPlayers( cx );
            this.drawPlayerBids( cx );
            this.drawPlayerAnnounces( cx );
            this.drawPile( cx );
            
            canvasEl.style.cursor = this.cxCursor;
        }
        
        if ( this.animatedMove ) {
            this.animatedMove.draw( cx, this.cardWidth );
        }
        
        return 0;
    }
    
    drawBoard( cx: CanvasRenderingContext2D | null ): void
    {
        if ( ! cx ) {
            return;
        }
        
        cx.save();
        
        cx.fillStyle = this.theme.boardBackground;
        cx.roundRect(
            0,
            0,
            this.width,
            this.height,
            [8]
        );
        cx.fill();
        
        this.drawBorders( cx );
        
        cx.restore();
    }
    
    drawBorders( cx: CanvasRenderingContext2D ): void
    {
        // the border
        cx.strokeStyle = this.theme.border;
        cx.lineWidth = this.borderWidth;
        cx.beginPath();
        cx.roundRect(
            this.borderWidth / 2,
            this.borderWidth / 2,
            this.width - this.borderWidth,
            this.height - this.borderWidth,
            [8]
        );
        cx.stroke();
    }
    
    drawDeck( cx: CanvasRenderingContext2D ): void
    {
        if ( ! this.game ) {
            return;
        }
        
        if ( ! this.deck.length ) {
            return;
        }
        
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
        for ( let c = 0; c < this.game.deck.length; c++ ) {
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
    
    drawPlayerAnnounces( cx: CanvasRenderingContext2D ): void
    {
        if ( ! cx ) {
            return;
        }
        
        if ( ! this.game ) {
            return;
        }
        
        if ( ! this.playerAnnounces ) {
            return;
        }
        
        for ( let pa = 0; pa < this.playerAreas.length; pa++ ) {
            if ( this.playerAnnounces.hasOwnProperty( pa ) ) {
                //console.log( 'Player Announce', this.playerAnnounces[pa] );
                //alert( 'Announce Player: ' + pa + ' Announce Type: ' + this.playerAnnounces[pa].Type );
                this.drawPlayerAnnounce( this.playerAreas[pa], this.playerAnnounces[pa] );
            }
        }
    }
    
    drawCards( playerCards: CardDto[], playerPosition: number ): void
    {
        if ( ! this.game ) {
            return;
        }
        
        var highLight, card, pa, cardX, cardY, angle, xOffset = 0, yOffset = 0;
        var cardsWidth = this.cardWidth + ( ( playerCards.length - 1 ) * this.cardOffset );
        for ( let c = 0; c < playerCards.length; c++ ) {
            highLight = false;
            pa = this.playerAreas.find( ( x ) => x.playerPosition === playerPosition );
            if ( ! pa ) {
                continue;
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
                    
                    const area = this.cardAreas.find( ( r ) => r.cardIdx === playerCards[c].cardIndex );
                    if ( area ) {
                        highLight = area.hasValidCard;
                    }
                }
                
                cardX = pa.x + pa.width / 2 - ( cardsWidth / 2 ) + ( c * this.cardOffset );
                cardY = pa.y + yOffset;
                angle = 0;
            }
            
            var cardImagesPath;
            switch ( this.game.gameCode ) {
                case GameVariant.BRIDGE_BELOTE_CODE:
                    cardImagesPath = '/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/BridgeBelote';
                    break;
                default:
                    cardImagesPath = '/build/gameplatform-velzonsaas-theme/images/CardGame/Cards/ContractBridge';
            }
            
            Card.draw(
                this.cx,
                cardImagesPath,
                playerCards[c],
                { x: cardX, y: cardY },
                this.cardWidth,
                this.cardHeight,
                angle,
                this.theme,
                playerPosition,
                highLight,
                window.gamePlatformSettings.debugCardGamePlayerCards
            );
        }
    }
    
    drawPile( cx: CanvasRenderingContext2D ): void
    {
        if ( ! this.game ) {
            return;
        }
        
        // If There are any cards in deck don't draw the pile
        if ( this.deck.length ) {
            return;
        }
        
        if ( false ) {
            return this.debugDrawPile();
        }
        
        if ( ! this.pile.length ) {
            return;
        }
        
        Pile.drawAsPile(
            this.cx,
            this.pile,
            this.width,
            this.height,
            this.cardWidth,
            this.cardHeight,
            this.theme
        );
    }
    
    initPlayerAreas(): void
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
    
    initCardAreas(): void
    {
        if ( ! this.game ) {
            return;
        }
        
        if ( ! this.playerCards ) {
            return;
        }
        
        const playerCards = this.playerCards[PlayerPosition.south]
        const cardsWidth = this.cardWidth + ( ( playerCards.length - 1 ) * this.cardOffset );
        
        const pa = this.playerAreas.find( ( x ) => x.playerPosition === PlayerPosition.south );
        if ( ! pa ) {
            return;
        }
        
        const yOffset = pa.height - this.cardHeight;
        const cardY = pa.y + yOffset;
        for ( let c = 0; c < playerCards.length; c++ ) {
            let cardX = pa.x + pa.width / 2 - ( cardsWidth / 2 ) + ( c * this.cardOffset );
            let areaWidth = c == ( playerCards.length - 1 ) ? this.cardWidth : this.cardOffset;
            
            this.cardAreas[c].set( cardX, cardY, areaWidth, this.cardHeight, playerCards[c].cardIndex );
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
    
    drawPlayerAnnounce( playerArea: CardGamePlayerArea, playerAnnounces: AnnounceDto[] ): void
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
        
        for ( let a = 0; a < playerAnnounces.length; a++ ) {
            //console.log( 'Announce', playerAnnounces[a] );
            //console.log( 'Announce Type', playerAnnounces[a].Type );
            this.cx.fillText( this.translateService.instant( this.announceTypes[playerAnnounces[a].Type] ), 0, 0 );
        }
        
        this.cx.restore();
    }
    
    onTouchStart( event: TouchEvent ): void
    {
        //console.log( 'touch start', event );
        this.hasTouch = true;
        if ( event.touches.length !== 1 ) {
            return;
        }
        const touch = event.touches[0];
        const { x, y } = this.getTouchPoint( touch );
        
        this.lastTouch = { x, y };
        
        this.cursor.x = x;
        this.cursor.y = y;
        
        // console.log( 'touchstart', x, y );
        
        this.handleDown( x, y );
        this.setCanBePlayed( x, y );
        //this.setCanBeMovedTo( x, y );
    }
    
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    onTouchEnd( event: TouchEvent ): void
    {
        //console.log( 'touch end', event );
        
        if ( this.cursor != undefined ) {
            this.handleUp( this.cursor.x, this.cursor.y );
        }
        this.lastTouch = undefined;
    }
    
    onTouchMove( event: TouchEvent ): void
    {
        //console.log( 'touch move', event );
        if ( event.touches.length !== 1 ) {
            return;
        }
        const touch = event.touches[0];
        const { x, y } = this.getTouchPoint( touch );
        
        this.lastTouch = { x, y };
        const w = this.cardWidth;
        
        this.handleMove( x - w / 2, y - w / 2 );
    }
    
    debugDrawPile(): void
    {
        if ( ! this.playerCards ) {
            return;
        }
        
        var pile = [
            this.playerCards[PlayerPosition.south][0],
            this.playerCards[PlayerPosition.east][0],
            this.playerCards[PlayerPosition.north][0],
            this.playerCards[PlayerPosition.west][0]
        ];
        
        Pile.drawAsPile(
            this.cx,
            pile,
            this.width,
            this.height,
            this.cardWidth,
            this.cardHeight,
            this.theme
        );
    }
}
