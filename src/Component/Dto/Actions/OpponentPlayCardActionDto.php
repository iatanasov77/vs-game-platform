<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerPosition;
use App\Component\Dto\CardDto;

class OpponentPlayCardActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::opponentPlayCard->value;
    }
    
    public CardDto $Card;
    
    public bool $Belote;
    
    public PlayerPosition $Player;
    
    public int $TrickNumber;
}
