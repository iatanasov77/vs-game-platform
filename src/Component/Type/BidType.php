<?php namespace App\Component\Type;

enum BidType: int
{
    case Pass       = 0;
    case Clubs      = 1; // ♣
    case Diamonds   = 2; // ♦
    case Hearts     = 3; // ♥
    case Spades     = 4; // ♠
    
    case NoTrumps   = 5;
    case AllTrumps  = 6;
    case Double     = 7;
    case ReDouble   = 8;
}
