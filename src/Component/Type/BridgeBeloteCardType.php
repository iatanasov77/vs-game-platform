<?php namespace App\Component\Type;

enum BridgeBeloteCardType: int
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
            BridgeBeloteCardType::Seven => '7',
            BridgeBeloteCardType::Eight => '8',
            BridgeBeloteCardType::Nine => '9',
            BridgeBeloteCardType::Ten => '10',
            BridgeBeloteCardType::Jack => 'J',
            BridgeBeloteCardType::Queen => 'Q',
            BridgeBeloteCardType::King => 'K',
            BridgeBeloteCardType::Ace => 'A',
        };
    }
}
