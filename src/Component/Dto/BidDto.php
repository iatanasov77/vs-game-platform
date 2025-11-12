<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class BidDto
{
    public PlayerPosition $Player;
    public ?PlayerPosition $KontraPlayer = NULL;
    public ?PlayerPosition $ReKontraPlayer = NULL;
    
    public int $Type; // BidType
    public Collection $NextBids; // BidDto[]
}
