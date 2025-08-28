<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

class GameCreatedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::gameCreated->value;
    }
    
    public GameDto $game;
    public ?PlayerColor $myColor;
    public ?PlayerPosition $myPosition;
}
