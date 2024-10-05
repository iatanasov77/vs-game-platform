<?php namespace App\Component\Dto\Actions;

use Doctrine\Common\Collections\Collection;

class HintMovesActionDto extends ActionDto
{
    public Collection $moves; // MoveDto[]
    
    public function  __construct()
    {
        $this->actionName = ActionNames::hintMoves;
    }
}
