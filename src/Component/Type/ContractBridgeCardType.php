<?php namespace App\Component\Type;

enum ContractBridgeCardType: int
{
    case Two    = 0;
    case Three  = 1;
    case Four   = 2;
    case Five   = 3;
    case Six    = 4;
    case Seven  = 5;
    case Eight  = 6;
    case Nine   = 7;
    case Ten    = 8;
    case Jack   = 9;
    case Queen  = 10;
    case King   = 11;
    case Ace    = 12;
    
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
