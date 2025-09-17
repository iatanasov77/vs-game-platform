<?php namespace App\Component\Dto;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class BidDto
{
    public PlayerPosition $Player;
    
    public BidType $Type;
}
