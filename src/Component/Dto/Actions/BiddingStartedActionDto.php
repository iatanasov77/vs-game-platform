<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerPosition;

class BiddingStartedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::biddingStarted->value;
    }
    
    public array $playerCards;
    public ?PlayerPosition $playerToBid;
    public array $validBids;
    public int $bidTimer;
}
