<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\BidType;
use App\Component\Type\CardSuit;

class BidTypeExtensions
{
    public static function ToCardSuit( BidType $bidType ): CardSuit
    {
        return $bidType->has( BidType::Clubs ) ? CardSuit::Club :
                $bidType->has( BidType::Diamonds ) ? CardSuit::Diamond :
                $bidType->has( BidType::Hearts ) ? CardSuit::Heart :
                $bidType->has( BidType::Spades ) ? CardSuit::Spade : 
                throw new \RuntimeException( 'BidTypeExtensions Error' );
    }
    
}
