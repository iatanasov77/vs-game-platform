<?php namespace App\Component\Rules\CardGame\Context;

use Doctrine\Common\Collections\Collection;

class PlayerGetAnnouncesContext extends BasePlayerContext
{
    public array $Announces;
    
    public array $CurrentTrickActions;
    
    public Collection $AvailableAnnounces;
}
