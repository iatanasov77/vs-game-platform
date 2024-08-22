require( '../Einaregilsson_Cards.Js/deckType' );
const cards = require( '../Einaregilsson_Cards.Js/cards' );

import AbstractGame from './AbstractGame';
import ICardGame from './CardGameInterface';
import CardGamePlayer from './CardGamePlayer';
import GamePlayersIterator from './GamePlayersIterator';

import Announce from '../CardGameAnnounce/Announce';
import BeloteCardGameAnnounce from '../CardGameAnnounce/BeloteCardGameAnnounce';
import * as GameEvents from './GameEvents';

declare var $: any;
declare global {
    interface Window {
        playerAnnounce: any;
    }
}

class BeloteCardGame extends AbstractGame implements ICardGame
{
    /** Cards Deck */
    deck: any;
    
    /** Game Players */
    players: GamePlayersIterator;
    
    /** Players Hands */
    handKeys: Array<string>;
    
    /** Current Dealer */
    currentDealer: number;
    
    /** Assync Function */
    waitMyAnnounce: any;
    
    waitAnnounces: any;
    
    /** Array */
    announces: any;
    
    constructor( id: string, publicRootPath: string, boardSelector: string = '#card-table' )
    {
        super( id, publicRootPath, boardSelector );
        
        //Now lets create a couple of hands, one face down, one face up.
        this.players  = new GamePlayersIterator([
            ( new CardGamePlayer( 'left', 'LeftPlayer', 'Left Player', 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:75, y:225 }) ),
            ( new CardGamePlayer( 'top', 'TopPlayer', 'Top Player', 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:335, y:52 }) ),
            ( new CardGamePlayer( 'right', 'RightPlayer', 'Right Player', 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:605, y:227 }) ),
            ( new CardGamePlayer( 'bottom', 'BottomPlayer', 'Bottom Player', 'player', true ) ).setHand( new cards.Hand({ faceUp:true, x:335, y:415 }) ),
        ], false);
        
        this.handKeys   = ['lefthand', 'upperhand', 'righthand', 'lowerhand'];
        
        this.currentDealer  = 4;
        //this.currentDealer  = 2;
    }
    
    public override initBoard(): void
    {
        //Start by initalizing the library
        cards.init({
            type: BELOTE,
            table: this.boardSelector,
            cardsUrl: this.publicRootPath + '/einaregilsson-cards.js/img/cards.png'
        });
        //console.log( cards.all );
        
        //Create a new deck of cards
        this.deck    = new cards.Deck();
        
        //cards.all contains all cards, put them all in the deck
        this.deck.addCards( cards.all );
        
        //No animation here, just get the deck onto the table.
        this.deck.render( {immediate:true} );
    }
    
    public override startGame(): void
    {
        // Start Game
        this.dealCards( 5 );
        
        this.announces   = new Array();
        this.startAnnounce();
    }
    
    public override nextGame(): void
    {
        this.currentDealer++
    }
    
    public override getHands(): any
    {
        let hands   = new Map();
        this.players.setStartIterationIndex( this.currentDealer - 1 );
        do {
            hands.set( this.handKeys[this.players.getCurrentIndex()], this.players.geCurrentPlayer().getHand() );
        } while( this.players.nextPlayer().done === false );
        
        return hands;
    }
    
    public getPlayers(): Array<CardGamePlayer>
    {
        return this.players.getPlayers();
    }
    
    public dealCards( count: number ): void
    {
        let hands   = this.getHands();
        
        //Deck has a built in method to deal to hands.
        this.deck.deal( count, Array.from( hands.values() ), 50, function() {
            let lefthand    = hands.get( 'lefthand' );
            let upperhand   = hands.get( 'upperhand' );
            let righthand   = hands.get( 'righthand' );
            let lowerhand   = hands.get( 'lowerhand' );
            let i: number;

            for ( i = 0; i < lefthand.length; i++ ) {
                lefthand[i].rotate( 90 );
                
                lefthand[i].el.css( 'left', 10 + 'px' );
                lefthand[i].el.css( 'top', ( i * 20 ) + 'px' );
                
                lefthand[i].el.moveTo( '#lefthand' );   // https://stackoverflow.com/questions/2596833/how-to-move-child-element-from-one-parent-to-another-using-jquery
            }
            
            for ( i = 0; i < upperhand.length; i++ ) {
                upperhand[i].el.css( 'left', 10 + ( i * 20 ) + 'px' );
                upperhand[i].el.css( 'top', '0px' );
                
                upperhand[i].el.moveTo( '#upperhand' );   // https://stackoverflow.com/questions/2596833/how-to-move-child-element-from-one-parent-to-another-using-jquery
            }
            
            for ( i = 0; i < righthand.length; i++ ) {
                righthand[i].rotate( 90 );
                
                righthand[i].el.css( 'left', 60 + 'px' );
                righthand[i].el.css( 'top', ( i * 20 ) + 'px' );
                
                righthand[i].el.moveTo( '#righthand' ); // https://stackoverflow.com/questions/2596833/how-to-move-child-element-from-one-parent-to-another-using-jquery
            }
            
            for ( i = 0; i < lowerhand.length; i++ ) {
                lowerhand[i].el.css( 'left', 10 + ( i * 20 ) + 'px' );
                lowerhand[i].el.css( 'top', '45px' );

                lowerhand[i].el.moveTo( '#lowerhand' );   // https://stackoverflow.com/questions/2596833/how-to-move-child-element-from-one-parent-to-another-using-jquery
            }
        });
    }
    
    public initAnnounceEventListeners()
    {
        let promise = new Promise( ( resolve ) => {
            //alert( resolve );
            $( '#BottomPlayer' ).get( 0 ).addEventListener( GameEvents.PLAYER_ANNOUNCE_EVENT_NAME, resolve );
        });
        
        this.waitMyAnnounce = async function waitMyAnnounce() {
            return await promise.then( ( ev: any ) => {
                const { announceId }    = ev.detail;
                window.playerAnnounce   = announceId;
            });
        }
    }
    
    public afterAnnounce( player: CardGamePlayer, oAnnounce: BeloteCardGameAnnounce )
    {
        let setImmediate = global.setImmediate || ( ( fn: any, ...args: any[] ) => global.setTimeout( fn, 0, ...args ) );
        const unblock = () => new Promise( setImmediate );
        
        const waitForLength = async ( arr: any, len: any ) => {
            while ( true ) {
                if ( arr.length >= len )
                    return arr;
                else
                    await unblock();
            }
        }
        
        this.waitAnnounces = async () => {
            const result    = await waitForLength( this.announces, 4 );
            return result;
        }
        
        this.waitAnnounces().then( () => {
            let announce    = oAnnounce.getAnnounce( this.announces );
            
            // Deal After Anounce If The Announce is not PASS
            if ( announce == Announce.PASS ) {
                $( '#btnStartGame' ).show();
            } else {
                this.dealCards( 3 );
                let pile    = this.beginPlaying( player.getHand() );
                
                $( this.boardSelector ).get( 0 ).dispatchEvent(
                    new CustomEvent( GameEvents.GAME_START_EVENT_NAME, {
                        detail: {
                            announceId: announce
                        },
                    })
                );
            }
        });
    }
    
    startAnnounce()
    {
        this.initAnnounceEventListeners();
        
        let waitTimeout;
        let player: CardGamePlayer;
        let nextPlayer: CardGamePlayer; // Using for current Iteration
        let lastAnnounce;
        let oAnnounce       = new BeloteCardGameAnnounce();
        let loopIndex       = 1;
        let waitMyAnnounce  = false;
        
        // https://developer.mozilla.org/en-US/docs/Web/API/setTimeout
        const partnerBoundMethod = ( function ( this: BeloteCardGame, containerId: any, lastAnnounce: any ) {
            this.fireAnnounceEvent( containerId, lastAnnounce );
        }).bind( this );
        
        const playerBoundMethod = ( function ( announceContainerId: any ) {
            $( '#' + announceContainerId ).show();
        }).bind( this );
      
        this.players.setStartIterationIndex( this.currentDealer -1 );
        do {
            nextPlayer  = this.players.geCurrentPlayer();
            
            waitTimeout = loopIndex * 2000;
            
            if ( nextPlayer.type == 'player' ) {
                waitMyAnnounce  = true;
                player          = nextPlayer;
                setTimeout( playerBoundMethod, waitTimeout, 'AnnounceContainer' );
                
                // Wait For Player Announce
                this.waitMyAnnounce()
                    .then(() => {
                        lastAnnounce    = window.playerAnnounce;
                        this.announces.push( lastAnnounce );
                        //alert( 'My Announce: ' + window.playerAnnounce );
                        
                        this.fireAnnounceEvent( player.containerId, lastAnnounce );
                        this.continueAnnounce( ++loopIndex );
                    });
                    
                // After Announce Begin Playing
                this.afterAnnounce( player, oAnnounce );
            } else {
                if ( ! waitMyAnnounce ) {
                    // Create Announce for Partner Gamer
                    lastAnnounce    = oAnnounce.announce( nextPlayer.getHand(), lastAnnounce );
                    this.announces.push( lastAnnounce );
                    
                    nextPlayer.setAnnounce( lastAnnounce );
                    
                    setTimeout( partnerBoundMethod, waitTimeout, nextPlayer.containerId, lastAnnounce );
                }
            }
            
            loopIndex++;
        } while( this.players.nextPlayer().done === false );
    }
    
    /**
     * Continue Announce After My Announce
     */
    continueAnnounce( loopIndex: number )
    {
        let waitTimeout;
        let nextPlayer: CardGamePlayer; // Using for current Iteration
        let lastAnnounce;
        let oAnnounce   = new BeloteCardGameAnnounce();
        
        // https://developer.mozilla.org/en-US/docs/Web/API/setTimeout
        const partnerBoundMethod = ( function ( this: BeloteCardGame, containerId: any, lastAnnounce: any ) {
            this.fireAnnounceEvent( containerId, lastAnnounce );
        }).bind( this );
        
        do {
            nextPlayer  = this.players.geCurrentPlayer();
            
            waitTimeout = loopIndex * 2000;
            
            // Create Announce for Partner Gamer
            lastAnnounce    = oAnnounce.announce( nextPlayer.getHand(), lastAnnounce );
            this.announces.push( lastAnnounce );
            
            nextPlayer.setAnnounce( lastAnnounce );
            
            setTimeout( partnerBoundMethod, waitTimeout, nextPlayer.containerId, lastAnnounce );
            
            loopIndex++;
        } while( this.players.nextPlayer().done === false );
    }
    
    beginPlaying( playerHand: any )
    {
        let pile    = new cards.Deck( {faceUp:true} );
        
        let leftOffset = 20;
        playerHand.click( function( card: any )
        {
            pile.addCard( card );
            pile.render({
                callback: function() {
                    for ( let i = 0; i < pile.length; i++ ) {
                        pile[i].rotate( -60 + ( ( i + 1 ) * 25 ) );
                        pile[i].el.moveTo( '#card-table' );
                        
                        var left = parseInt( $( pile[i].el ).css( 'left' ) );
                        pile[i].el.css( 'left', ( left - leftOffset ) + 'px' );
                        leftOffset  = leftOffset - 10;
                    }
                }
            });
            
            //alert( card.suit );
            //alert( card.rank );
        });
        
        return pile;
    }
    
    fireAnnounceEvent( playerContainerId: any, announceId: any )
    {
        $( "#" + playerContainerId ).get( 0 ).dispatchEvent(
            new CustomEvent( GameEvents.PLAYER_ANNOUNCE_EVENT_NAME, {
                detail: {
                    announceId: announceId
                },
            })
        );
    }
}

export default BeloteCardGame;
