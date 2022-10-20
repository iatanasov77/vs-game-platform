require( '../../library/GamePlatform/Einaregilsson_Cards.Js/deckType' );
const cards = require( '../../library/GamePlatform/Einaregilsson_Cards.Js/cards' );

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

function startGame( deck )
{
    //Now lets create a couple of hands, one face down, one face up.
    let lefthand    = new cards.Hand({ faceUp:false, x:75, y:225 });
    let upperhand   = new cards.Hand({ faceUp:false, y:52 });
    let righthand   = new cards.Hand({ faceUp:false, x:605, y:227 });
    let lowerhand   = new cards.Hand({ faceUp:true, y:415 });
    
    let hands       = [lefthand, upperhand, righthand, lowerhand];
    
    //Deck has a built in method to deal to hands.
    deck.deal( 5, hands, 50, function() {
        let i;
        
        for ( i = 0; i < lefthand.length; i++ ) {
            lefthand[i].rotate( 90 );
            lefthand[i].el.css( 'left', 20 + 'px' );
            lefthand[i].el.css( 'top', ( 115 + ( i * 20 ) ) + 'px' );
        }
        
        for ( i = 0; i < righthand.length; i++ ) {
            righthand[i].rotate( 90 );
            righthand[i].el.css( 'left', 595 + 'px' );
            righthand[i].el.css( 'top', ( 115 + ( i * 20 ) ) + 'px' );
        }
    });
    
    return hands;
}

function getAnnounce( hands )
{
    let lastAnnounce;
    let oAnnounce   = new BeloteCardGameAnnounce();
    let announces   = new Array() ;
    
    for ( let i = 0; i < hands.length; i++ ) {
        lastAnnounce    = oAnnounce.announce( hands[i], lastAnnounce )
        announces.push( lastAnnounce );
    }
    
    return oAnnounce.getAnnounce( announces );
}

$( function()
{
    let hands;
    let deck    = initCardsDeck();
    
    $( '#btnStartGame' ).on( 'click', function ( e )
    {
        e.preventDefault();
        
        let hands       = startGame( deck );
        let announce    = getAnnounce( hands );
        let rules       = new BeloteGameRules().rules( announce );
        
        // Deal After Anounce If The Announce is not PASS
        if ( announce == Announce.PASS ) {
            
        } else {
            deck.deal( 3, hands, 50 );
        }
        
        //alert( announce );
        //alert( rules );
    });
});
