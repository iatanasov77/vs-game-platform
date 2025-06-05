<?php namespace App\Component\Dto\Actions;

use Doctrine\Common\Collections\Collection;

class MovesMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::movesMade->value;
    }
    
    public Collection $moves; // MoveDto[]
}
