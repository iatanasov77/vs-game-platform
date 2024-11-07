<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Type\PlayerColor;

class StartGamePlayActionDto extends ActionDto
{
    public function  __construct()
    {
        $this->actionName = ActionNames::startGamePlay->value;
    }
    
    public GameDto $game;
    public PlayerColor $myColor;
}
