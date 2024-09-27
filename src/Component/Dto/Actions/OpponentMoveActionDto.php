<?php namespace App\Component\Dto\Actions;

use Doctrine\Common\Collections\Collection;
use App\Component\Dto\MoveDto;

class OpponentMoveActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::opponentMove;
    }
    
    public MoveDto $move;
}
