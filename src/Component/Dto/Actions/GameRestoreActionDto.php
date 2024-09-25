<?php namespace App\Component\Dto\Actions;

use Doctrine\Common\Collections\Collection;
use App\Component\Dto\GameDto;
use App\Component\Type\PlayerColor;

class GameRestoreActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::gameRestore;
    }
    
    public GameDto $game;
    public PlayerColor $color;
    public Collection $dices; // DiceDto[]
}
