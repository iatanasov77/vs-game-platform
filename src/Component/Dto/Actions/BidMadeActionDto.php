<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class BidMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::bidMade->value;
    }
    
    public PlayerPosition $Player;
    
    public BidType $Type;
}
