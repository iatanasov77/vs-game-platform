require( '../Einaregilsson_Cards.Js/deckType' );
const cards = require( '../Einaregilsson_Cards.Js/cards' );

require ( '../../../includes/jquery.moveTo.js' );

import AbstractGame from './AbstractGame';
import CardGamePlayer from './CardGamePlayer';

import Announce from '../CardGameAnnounce/Announce';
import BeloteCardGameAnnounce from '../CardGameAnnounce/BeloteCardGameAnnounce';
import * as GameEvents from './GameEvents';

class BeloteCardGame extends AbstractGame
{
    /**
     * Cards Deck
     */
    deck;
    
    /**
     * Game Players
     */
    players;
    
    /**
     * Assync Function
     */
    waitMyAnnounce;
    
    /**
     * Array
     */
    announces;
    
    
    constructor( boardSelector )
    {
        super( boardSelector );
        
        //Now lets create a couple of hands, one face down, one face up.
        this.players  = [
            ( new CardGamePlayer( 'left', 'LeftPlayer', 'Left Player', 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:75, y:225 }) ),
            ( new CardGamePlayer( 'top', 'TopPlayer', 'Top Player', 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:335, y:52 }) ),
            ( new CardGamePlayer( 'right', 'RightPlayer', 'Right Player', 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:605, y:227 }) ),
            ( new CardGamePlayer( 'bottom', 'BottomPlayer', 'Bottom Player', 'player' ) ).setHand( new cards.Hand({ faceUp:true, x:335, y:415 }) )
        ];
    }
    
    initBoard()
    {
        //Start by initalizing the library
        cards.init({
            type: BELOTE,
            table: this.boardSelector,
            cardsUrl: '/build/card-game/einaregilsson-cards.js/img/cards.png'
        });
        //console.log( cards.all );
        
        //Create a new deck of cards
        this.deck    = new cards.Deck();
        //cards.all contains all cards, put them all in the deck
        this.deck.addCards( cards.all );
        
        //No animation here, just get the deck onto the table.
        this.deck.render( {immediate:true} );
    }
    
    startGame()
    {
        // Start Game
        this.dealCards( 5 );
        
        this.announces   = new Array() ;
        this.startAnnounce();
    }
    
    dealCards( count )
    {
        let lefthand    = this.players[0].getHand();
        let upperhand   = this.players[1].getHand();
        let righthand   = this.players[2].getHand();
        let lowerhand   = this.players[3].getHand();
        
        //Deck has a built in method to deal to hands.
        this.deck.deal( count, [lefthand, upperhand, righthand, lowerhand], 50, function() {
            let i;
            
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
    
    initAnnounceEventListeners()
    {
        let promise = new Promise( ( resolve ) => {
            $( '#AnnounceContainer' ).find( '#btnClover' ).get( 0 ).addEventListener( 'click', resolve );
            $( '#AnnounceContainer' ).find( '#btnDiamond' ).get( 0 ).addEventListener( 'click', resolve );
            $( '#AnnounceContainer' ).find( '#btnHeart' ).get( 0 ).addEventListener( 'click', resolve );
            $( '#AnnounceContainer' ).find( '#btnSpade' ).get( 0 ).addEventListener( 'click', resolve );
            
            $( '#AnnounceContainer' ).find( '#btnBezKoz' ).get( 0 ).addEventListener( 'click', resolve );
            $( '#AnnounceContainer' ).find( '#btnVsichkoKoz' ).get( 0 ).addEventListener( 'click', resolve );
            $( '#AnnounceContainer' ).find( '#btnPass' ).get( 0 ).addEventListener( 'click', resolve );
        });
        
        this.waitMyAnnounce = async function waitMyAnnounce() {
            return await promise.then( ( ev ) => {
                ev.preventDefault();
                
                window.playerAnnounce  = $( ev.target ).parent( 'a' ).attr( 'data-announce' );
            });
        }
    }
    
    afterAnnounce( player, oAnnounce )
    {
        let setImmediate = global.setImmediate || ( ( fn, ...args ) => global.setTimeout( fn, 0, ...args ) );
        const unblock = () => new Promise( setImmediate );
        
        const waitForLength = async ( arr, len ) => {
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
            $( this.boardSelector ).get( 0 ).dispatchEvent(
                new CustomEvent( GameEvents.GAME_START_EVENT_NAME, {
                    detail: {
                        announceId: announce
                    },
                })
            );
            
            // Deal After Anounce If The Announce is not PASS
            if ( announce == Announce.PASS ) {
                
            } else {
                this.dealCards( 3 );
                let pile    = this.beginPlaying( player.getHand() );
            }
        });
    }
    
    startAnnounce()
    {
        this.initAnnounceEventListeners();
        
        let waitTimeout;
        let player;
        let lastAnnounce;
        let oAnnounce   = new BeloteCardGameAnnounce();
        
        // https://developer.mozilla.org/en-US/docs/Web/API/setTimeout
        const partnerBoundMethod = ( function ( containerId, lastAnnounce ) {
            this.fireAnnounceEvent( containerId, lastAnnounce );
        }).bind( this );
        
        const playerBoundMethod = ( function ( announceContainerId ) {
            $( '#' + announceContainerId ).show();
        }).bind( this );
        
        for ( let i = 0; i < this.players.length; i++ ) {
            waitTimeout = ( i + 1 ) * 2000;
            
            if ( this.players[i].type == 'player' ) {
                player  = this.players[i];
                setTimeout( playerBoundMethod, waitTimeout, 'AnnounceContainer' );
                
                // Wait For Player Announce
                this.waitMyAnnounce()
                    .then(() => {
                        lastAnnounce    = window.playerAnnounce;
                        this.announces.push( lastAnnounce );
                        //alert( 'My Announce: ' + window.playerAnnounce );
                        
                        this.fireAnnounceEvent( this.players[i].containerId, lastAnnounce );
                    });
            } else {
                // Create Announce for Partner Gamer
                lastAnnounce    = oAnnounce.announce( this.players[i].getHand(), lastAnnounce );
                this.announces.push( lastAnnounce );
                
                this.players[i].setAnnounce( lastAnnounce );
                
                setTimeout( partnerBoundMethod, waitTimeout, this.players[i].containerId, lastAnnounce );
            }
        }
        
        this.afterAnnounce( player, oAnnounce );
    }
    
    beginPlaying( playerHand )
    {
        let pile    = new cards.Deck( {faceUp:true} );
        
        let leftOffset = 20;
        playerHand.click( function( card )
        {
            pile.addCard( card );
            pile.render({
                callback: function() {
                    for ( let i = 0; i < pile.length; i++ ) {
                        pile[i].rotate( -60 + ( ( i + 1 ) * 25 ) );
                        
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
    
    fireAnnounceEvent( playerContainerId, announceId )
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
