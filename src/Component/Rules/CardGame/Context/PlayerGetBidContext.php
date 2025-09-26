<?php namespace App\Component\Rules\CardGame\Context;

use BitMask\EnumBitMask;

class PlayerGetBidContext extends BasePlayerContext
{
    public EnumBitMask $AvailableBids;
}
