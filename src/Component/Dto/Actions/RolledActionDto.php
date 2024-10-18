<?php namespace App\Component\Dto\Actions;

class RolledActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::rolled->value;
    }
}
