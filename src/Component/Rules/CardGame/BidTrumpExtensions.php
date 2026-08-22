<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\BidTrump;
use App\Component\Type\CardSuit;

class BidTrumpExtensions
{
    public static function ToCardSuit( BidTrump $bidTrump ): CardSuit
    {
        switch ( $bidTrump ) {
            case BidTrump::Clubs:
                return CardSuit::Club;
                break;
            case BidTrump::Diamonds:
                return CardSuit::Diamond;
                break;
            case BidTrump::Hearts:
                return CardSuit::Heart;
                break;
            case BidTrump::Spades:
                return CardSuit::Spade;
                break;
            default:
                throw new \RuntimeException( 'BidTrumpExtensions Error' );
        }
    }
}
