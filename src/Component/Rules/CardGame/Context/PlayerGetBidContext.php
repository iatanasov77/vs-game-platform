<?php namespace App\Component\Rules\CardGame\Context;

use Doctrine\Common\Collections\Collection;

class PlayerGetBidContext extends BasePlayerContext
{
    public Collection $AvailableBids;
}
