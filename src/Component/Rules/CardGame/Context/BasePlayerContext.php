<?php namespace App\Component\Rules\CardGame\Context;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;

class BasePlayerContext
{
    public int $RoundNumber;
    
    public PlayerPosition $FirstToPlayInTheRound;
    
    public PlayerPosition $MyPosition;
    
    public int $SouthNorthPoints;
    
    public int $EastWestPoints;
    
    public Collection $MyCards;
    
    public array $Bids;
    
    public Bid $CurrentContract;
}
