<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\MoveDto;

class OpponentMoveActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::opponentMove->value;
    }
    
    public MoveDto $move;
}
