require( '../../library/GamePlatform/Einaregilsson_Cards.Js/deckType' );
const cards = require( '../../library/GamePlatform/Einaregilsson_Cards.Js/cards' );

require ( '../../includes/jquery.moveTo.js' );

import Announce from '../../library/GamePlatform/CardGameAnnounce/Announce';
import BeloteCardGameAnnounce from '../../library/GamePlatform/CardGameAnnounce/BeloteCardGameAnnounce';
import BeloteGameRules from '../../library/GamePlatform/GameRules/BeloteGameRules';

/**
 * A single Belot game room for 4 players - Angular 8 frontend.
 * --------------------------------------------------------------
 * https://github.com/Marina-Banov/bela-client
 *
 * Belot card game engine written in C#
 * --------------------------------------
 * https://github.com/NikolayIT/BelotGameEngine
 */

const beloteTable   = '#card-table';

function initCardsDeck()
{
    //Start by initalizing the library
    cards.init({
        type: BELOTE,
        table: beloteTable,
        cardsUrl: '/build/card-game/einaregilsson-cards.js/img/cards.png'
    });
    //console.log( cards.all );
    
    //Create a new deck of cards
    let deck    = new cards.Deck();
    //cards.all contains all cards, put them all in the deck
    deck.addCards( cards.all );
    
    //No animation here, just get the deck onto the table.
    deck.render( {immediate:true} );
    
    return deck;
}

function initGame( playerHand )
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

//Now lets create a couple of hands, one face down, one face up.
let lefthand    = new cards.Hand({ faceUp:false, x:75, y:225 });
let upperhand   = new cards.Hand({ faceUp:false, x:335, y:52 });
let righthand   = new cards.Hand({ faceUp:false, x:605, y:227 });
let lowerhand   = new cards.Hand({ faceUp:true, x:335, y:415 });

let hands       = [lefthand, upperhand, righthand, lowerhand];

function dealCards( count, deck )
{
    //Deck has a built in method to deal to hands.
    deck.deal( count, hands, 50, function() {
        let i;
        
        for ( i = 0; i < lefthand.length; i++ ) {
            lefthand[i].rotate( 90 );
            //lefthand[i].el.css( 'left', 20 + 'px' );
            //lefthand[i].el.css( 'top', ( 115 + ( i * 20 ) ) + 'px' );
            
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
    
    return hands;
}

let promise;
function startGame()
{
    $( '#AnnounceContainer' ).html( $( '#AnnounceButtons' ).html() );
    
    promise = new Promise((resolve, reject) => {
        $( '#AnnounceContainer' ).find( '#btnClover' ).get( 0 ).addEventListener( 'click', resolve );
        $( '#AnnounceContainer' ).find( '#btnDiamond' ).get( 0 ).addEventListener( 'click', resolve );
        $( '#AnnounceContainer' ).find( '#btnHeart' ).get( 0 ).addEventListener( 'click', resolve );
        $( '#AnnounceContainer' ).find( '#btnSpade' ).get( 0 ).addEventListener( 'click', resolve );
        
        $( '#AnnounceContainer' ).find( '#btnAce' ).get( 0 ).addEventListener( 'click', resolve );
        $( '#AnnounceContainer' ).find( '#btnJack' ).get( 0 ).addEventListener( 'click', resolve );
    });
}

let myAnnounce;
async function waitMyAnnounce() {
    return await promise.then( ( ev ) => {
        ev.preventDefault();
        
        myAnnounce  = $( ev.target ).parent( 'a' ).attr( 'data-announce' );
        // alert( myAnnounce );
    });
}

$( function()
{
    let hands;
    let deck    = initCardsDeck();
    
    $( '#btnClover' ).attr( 'data-announce', Announce.CLOVER );
    $( '#btnDiamond' ).attr( 'data-announce', Announce.DIAMOND );
    $( '#btnHeart' ).attr( 'data-announce', Announce.HEART );
    $( '#btnSpade' ).attr( 'data-announce', Announce.SPADE );
    $( '#btnAce' ).attr( 'data-announce', Announce.BEZ_KOZ );
    $( '#btnJack' ).attr( 'data-announce', Announce.VSICHKO_KOZ );
    
    $( '#btnStartGame' ).on( 'click', function ( e )
    {
        e.preventDefault();
        
        // Start Game
        let hands       = dealCards( 5, deck );
        startGame();
        
        let lastAnnounce;
        let oAnnounce   = new BeloteCardGameAnnounce();
        let announces   = new Array() ;
        
        for ( let i = 0; i < hands.length; i++ ) {
            //console.log( hands[i] );
            if ( hands[i].faceUp == true ) {
                waitMyAnnounce()
                    .then(() => {
                        lastAnnounce    = myAnnounce;
                        announces.push( lastAnnounce );
                        //alert( 'My Announce: ' + myAnnounce );
                        
                        if ( announces.length == 4 ) {
                            let announce    = oAnnounce.getAnnounce( announces );
                            //console.log( announces );
                            //alert( 'Game Announce: ' + announce );
                            let rules   = new BeloteGameRules().rules( announce );
                            
                            // Deal After Anounce If The Announce is not PASS
                            if ( announce == Announce.PASS ) {
                                
                            } else {
                                dealCards( 3, deck );
                                let pile    = initGame( lowerhand );
                                
                                $( '#AnnounceContainer' ).html( '<span class="announce-button">' + announce + '</span>' );
                            }
                        }
                    });
            } else {
                // Create Announce for Partner Gamer
                lastAnnounce    = oAnnounce.announce( hands[i], lastAnnounce );
                announces.push( lastAnnounce );
            }
        }
    });
});
