<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Dto\PlayerColor;

class GameCreatedActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::gameCreated;
    }
    
    public GameDto $game;
    public PlayerColor $myColor;
}
