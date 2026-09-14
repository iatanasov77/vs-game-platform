<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameDto;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

class GameRestoreActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::gameRestore->value;
    }
    
    public GameDto $game;
    public ?PlayerColor $myColor;
    
    public ?PlayerPosition $myPosition;
    public ?PlayerPosition $myTeamMate;
    
    public array $dices; // DiceDto[]
}
