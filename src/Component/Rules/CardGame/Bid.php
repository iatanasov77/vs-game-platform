<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class Bid
{
    public PlayerPosition $Player;
    
    public BidType $Type;
    
    public function __toString(): string
    {
        return "{$this->Type} ({$this->Player})";
    }
}
