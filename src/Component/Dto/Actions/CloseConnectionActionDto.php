<?php namespace App\Component\Dto\Actions;

class CloseConnectionActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::closeConnection->value;
    }
}