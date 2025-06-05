<?php namespace App\Component\Dto\Actions;

use App\Component\Type\PlayerColor;

class DicesRolledActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::dicesRolled->value;
    }
    
    public array $dices;
    public ?PlayerColor $playerToMove;
    public array $validMoves;
    public int $moveTimer;
    
    // @todo: maybe rewrite to have a relation between dice and move
}
