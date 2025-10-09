<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerPosition;
use App\Component\Dto\BidDto;

class PlayingStartedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::playingStarted->value;
    }
    
    public array $deck; // CardDto[]
    public array $playerCards;
    public ?PlayerPosition $firstToPlay;
    public BidDto $contract;
    public array $validCards;
    public int $timer;
}
