<?php namespace App\Component\Rules\CardGame\Context;

use Doctrine\Common\Collections\Collection;

class PlayerPlayCardContext extends BasePlayerContext
{
    // TODO: Don't disclose the exact type of announce
    public array $Announces;
    
    public Collection $CurrentTrickActions;
    
    public array $RoundActions;
    
    public Collection $AvailableCardsToPlay;
    
    public int $CurrentTrickNumber;
}
