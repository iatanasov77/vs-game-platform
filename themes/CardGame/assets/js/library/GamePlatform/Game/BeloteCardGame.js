require( '../Einaregilsson_Cards.Js/deckType' );
const cards = require( '../Einaregilsson_Cards.Js/cards' );

require ( '../../../includes/jquery.moveTo.js' );

import AbstractGame from './AbstractGame';
import CardGamePlayer from './CardGamePlayer';

import Announce from '../CardGameAnnounce/Announce';
import BeloteCardGameAnnounce from '../CardGameAnnounce/BeloteCardGameAnnounce';
import BeloteGameRules from '../GameRules/BeloteGameRules';

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
        
        
        //Now lets create a couple of hands, one face down, one face up.
        this.players  = [
            ( new CardGamePlayer( 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:75, y:225 }) ),
            ( new CardGamePlayer( 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:335, y:52 }) ),
            ( new CardGamePlayer( 'computer' ) ).setHand( new cards.Hand({ faceUp:false, x:605, y:227 }) ),
            ( new CardGamePlayer( 'player' ) ).setHand( new cards.Hand({ faceUp:true, x:335, y:415 }) )
        ];
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
            
            for ( i = 0; i < righthand.length; i++ ) {
                righthand[i].rotate( 90 );
                
                righthand[i].el.css( 'left', 595 + 'px' );
                righthand[i].el.css( 'top', ( 115 + ( i * 20 ) ) + 'px' );
                righthand[i].el.moveTo( '#righthand' ); // https://stackoverflow.com/questions/2596833/how-to-move-child-element-from-one-parent-to-another-using-jquery
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
            $( '#AnnounceContainer' ).html( '<span class="announce-button">' + announce + '</span>' );
            
            // Deal After Anounce If The Announce is not PASS
            if ( announce == Announce.PASS ) {
                
            } else {
                this.dealCards( 3 );
                let pile    = this.beginPlaying( player.getHand() );
                
                //let rules   = new BeloteGameRules().rules( announce );
            }
        });
    }
    
    startAnnounce()
    {
        this.initAnnounceEventListeners();
        
        let player;
        let lastAnnounce;
        let oAnnounce   = new BeloteCardGameAnnounce();
        
        for ( let i = 0; i < this.players.length; i++ ) {
            if ( this.players[i].type == 'player' ) {
                player  = this.players[i];
                
                // Wait For Player Announce
                this.waitMyAnnounce()
                    .then(() => {
                        lastAnnounce    = window.playerAnnounce;
                        this.announces.push( lastAnnounce );
                        //alert( 'My Announce: ' + window.playerAnnounce );
                    });
            } else {
                // Create Announce for Partner Gamer
                lastAnnounce    = oAnnounce.announce( this.players[i].getHand(), lastAnnounce );
                this.announces.push( lastAnnounce );
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
}

export default BeloteCardGame;
