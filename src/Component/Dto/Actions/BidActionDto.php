<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class BidActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::bid->value;
    }
    
    public PlayerPosition $Player;
    
    public BidType $Type;
}
