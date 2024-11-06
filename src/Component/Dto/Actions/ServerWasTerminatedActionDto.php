<?php namespace App\Component\Dto\Actions;

class ServerWasTerminatedActionDto extends ActionDto
{
    public function  __construct()
    {
        $this->actionName = ActionNames::serverWasTerminated->value;
    }
}
