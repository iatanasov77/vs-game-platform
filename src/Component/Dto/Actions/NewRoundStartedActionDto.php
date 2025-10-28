<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

class NewRoundStartedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::newRoundStarted->value;
    }
    
    public GameDto $game;
}
