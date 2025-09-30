<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class BidDto
{
    public PlayerPosition $Player;
    public BidType $Type;
    public Collection $NextBids; // BidDto[]
}
