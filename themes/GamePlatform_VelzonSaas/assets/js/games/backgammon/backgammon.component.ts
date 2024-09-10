import {
    Component,
    OnInit,
    OnDestroy,
    AfterViewInit,
    Inject,
    ElementRef,
    ViewChild,
    HostListener
} from '@angular/core';
import { Observable, Subscription } from 'rxjs';
import { Store } from '@ngrx/store';

import { AuthService } from '../application/services/auth.service'
import { GameService } from '../application/services/game.service'
import { SocketsService } from '../application/services/sockets.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

// App State
import { AppState } from '../application/state/app-state';
import { StatusMessage } from '../application/utils/status-message';
import { Busy } from '../application/state/busy';
import { StatusMessageService } from '../application/services/status-message.service';

// Board Interfaces
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import GameState from '_@/GamePlatform/Model/BoardGame/gameState';

//import { BridgeBeloteProvider } from '../application/providers/bridge-belote-provider';
//import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';

import cssGameString from './backgammon.component.scss';
import templateString from './backgammon.component.html';

@Component({
    selector: 'app-backgammon',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class BackgammonComponent extends GameBaseComponent implements OnInit, OnDestroy, AfterViewInit
{
    //game: BeloteCardGame;
    
    @ViewChild( 'dices' ) dices: ElementRef | undefined;
    @ViewChild( 'boardButtons' ) boardButtons: ElementRef | undefined;
    @ViewChild( 'messages' ) messages: ElementRef | undefined;
    
    gameDto$: Observable<GameDto>;
    dices$: Observable<DiceDto[]>;
    playerColor$: Observable<PlayerColor>;
    message$: Observable<StatusMessage>;
    timeLeft$: Observable<number>;
    gameSubs: Subscription;
    diceSubs: Subscription;
    
    width = 450;
    height = 450;
    rollButtonClicked = false;
    diceColor: PlayerColor | null = PlayerColor.neither;
    messageCenter = 0;
    flipped = false;
    
    rollButtonVisible = false;
    sendVisible = false;
    undoVisible = false;
    dicesVisible = false;
    newVisible = false;
    exitVisible = true;
    
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
        
        @Inject( SocketsService ) private socketsService: SocketsService,
        @Inject( StatusMessageService ) private statusMessageService: StatusMessageService,
    ) {
        super( authService, gameService, store );
        
        this.gameDto$ = AppState.Singleton.game.observe();
        this.dices$ = AppState.Singleton.dices.observe();
        this.diceSubs = AppState.Singleton.dices.observe().subscribe( this.diceChanged.bind( this ) );
        this.playerColor$ = AppState.Singleton.myColor.observe();
        this.gameSubs = AppState.Singleton.game.observe().subscribe( this.gameChanged.bind( this ) );
        this.message$ = AppState.Singleton.statusMessage.observe();
        this.timeLeft$ = AppState.Singleton.moveTimer.observe();
        
        // if game page is refreshed, restore user from login cookie
        if ( ! AppState.Singleton.user.getValue() ) {
            this.authService.repair();
        }
        
//         const gameId = this.router.parseUrl( this.router.url ).queryParams['gameId'];
//         const gameId = 'backgammon';
//         this.socketsService.connect( gameId );
    }
    
    override ngOnInit(): void
    {
        super.ngOnInit();
    }
    
    override ngOnDestroy(): void
    {
        super.ngOnDestroy();
        
        this.gameSubs.unsubscribe();
        this.diceSubs.unsubscribe();
    }
    
    sendMoves(): void
    {
        this.socketsService.sendMoves();
        this.rollButtonClicked = false;
    }
    
    doMove( move: MoveDto ): void
    {
        this.socketsService.doMove( move );
        this.socketsService.sendMove( move );
    }
    
    undoMove(): void
    {
        this.socketsService.undoMove();
        this.socketsService.sendUndo();
    }
    
    myTurn(): boolean
    {
        return AppState.Singleton.myTurn();
    }
    
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    gameChanged( dto: GameDto ): void
    {
        this.setRollButtonVisible();
        this.setDicesVisible();
        this.setSendVisible();
        this.setUndoVisible();
        this.diceColor = dto?.currentPlayer;
        this.fireResize();
        this.newVisible = dto?.playState === GameState.ended;
        this.exitVisible = dto?.playState !== GameState.playing;
    }
    
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    diceChanged( dto: DiceDto[] ): void
    {
        this.setRollButtonVisible();
        this.setDicesVisible();
        this.setSendVisible();
        this.setUndoVisible();
        this.fireResize();
        this.exitVisible = AppState.Singleton.game.getValue()?.playState !== GameState.playing;
    }
    
    moveAnimFinished(): void
    {
        this.socketsService.shiftMoveAnimationsQueue();
    }
    
    @HostListener( 'window:resize', ['$event'] )
    onResize(): void
    {
        this.width = Math.min( window.innerWidth, 1024 );
        const span = this.messages?.nativeElement as Element;
        const spanWidth = span.getElementsByTagName('span')[0].clientWidth;
        this.messageCenter = this.width / 2 - spanWidth / 2;
        
        this.height = Math.min( window.innerHeight - 40, this.width * 0.6 );
        
        const buttons = this.boardButtons?.nativeElement as HTMLElement;
        const btnsOffset = 5; //Cheating. Could not get the height.
        if ( buttons ) {
            buttons.style.top = `${this.height / 2 - btnsOffset}px`;
            buttons.style.right = `${this.width * 0.11}px`;
        }
        
        const dices = this.dices?.nativeElement as HTMLElement;
        if ( dices ) {
            // Puts the dices on right side if its my turn.
            if ( this.myTurn() ) {
                dices.style.left = `${this.width / 2 + 20}px`;
                dices.style.right = '';
            } else {
                dices.style.right = `${this.width / 2 + 20}px`;
                dices.style.left = '';
            }
            dices.style.top = `${this.height / 2 - btnsOffset}px`;
        }
    }
    
    ngAfterViewInit(): void
    {
        this.fireResize();
    }
    
    fireResize(): void
    {
        setTimeout( () => {
            this.onResize();
        }, 1);
    }
    
    rollButtonClick(): void
    {
        this.rollButtonClicked = true;
        this.setRollButtonVisible();
        this.setDicesVisible();
        this.setSendVisible();
        this.fireResize();
        const gme = AppState.Singleton.game.getValue();
        if ( ! gme.validMoves || gme.validMoves.length === 0 ) {
            this.statusMessageService.setBlockedMessage();
        }
    }
    
    setRollButtonVisible(): void
    {
        if ( ! this.myTurn() ) {
            this.rollButtonVisible = false;
            return;
        }
        
        this.rollButtonVisible = !this.rollButtonClicked;
    }
    
    setSendVisible(): void
    {
        if ( ! this.myTurn() || !this.rollButtonClicked ) {
            this.sendVisible = false;
            return;
        }
        
        const game = AppState.Singleton.game.getValue();
        this.sendVisible = ! game || game.validMoves.length == 0;
    }
    
    setUndoVisible(): void
    {
        if ( ! this.myTurn() ) {
            this.undoVisible = false;
            return;
        }
        
        const dices = AppState.Singleton.dices.getValue();
        this.undoVisible = dices && dices.filter( ( d ) => d.used ).length > 0;
    }
    
    setDicesVisible(): void
    {
        if ( ! this.myTurn() ) {
            this.dicesVisible = true;
            return;
        }
        this.dicesVisible = !this.rollButtonVisible;
    }
    
    resignGame(): void
    {
        this.socketsService.resignGame();
    }
    
    newGame(): void
    {
        this.newVisible = false;
//         this.socketsService.connect( '' );
    }
    
    exitGame(): void
    {
        this.socketsService.exitGame();
        Busy.hide();
        //this.router.navigateByUrl( '/lobby' );
    }
}
