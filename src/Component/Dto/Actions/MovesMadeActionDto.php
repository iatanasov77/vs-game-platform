<?php namespace App\Component\Dto\Actions;

class MovesMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::movesMade->value;
    }
    
    public array $moves; // MoveDto[]
}
