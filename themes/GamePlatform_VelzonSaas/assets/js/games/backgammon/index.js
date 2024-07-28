require( '../../../../../../assets/library/GamePlatform/Einaregilsson_Cards.Js/deckType' );
const cards = require( '../../../../../../assets/library/GamePlatform/Einaregilsson_Cards.Js/cards' );

$( function()
{
    //Start by initalizing the library
    cards.init({
        table: '#card-table',
        cardsUrl: '/build/game-platform-spa/einaregilsson-cards.js/img/cards.png'
    });
    //Create a new deck of cards
    deck = new cards.Deck(); 
    //cards.all contains all cards, put them all in the deck
    deck.addCards(cards.all); 
    //No animation here, just get the deck onto the table.
    deck.render({immediate:true});
});


