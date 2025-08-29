<?php namespace App\Component\Type;

enum CardType: int
{
    case Seven  = 0;
    case Eight  = 1;
    case Nine   = 2;
    case Ten    = 3;
    case Jack   = 4;
    case Queen  = 5;
    case King   = 6;
    case Ace    = 7;
    
    public function toString(): string
    {
        return match( $this ) {
            CardType::Seven => '7',
            CardType::Eight => '8',
            CardType::Nine => '9',
            CardType::Ten => '10',
            CardType::Jack => 'J',
            CardType::Queen => 'Q',
            CardType::King => 'K',
            CardType::Ace => 'A',
        };
    }
}
