<?php namespace App\Component\Dto\Actions;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;

class DicesRolledActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::dicesRolled->value;
    }
    
    public Collection $dices;
    public ?PlayerColor $playerToMove;
    public Collection $validMoves;
    public int $moveTimer;
    
    // @todo: maybe rewrite to have a relation between dice and move
}
