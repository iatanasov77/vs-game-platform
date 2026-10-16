<?php namespace App\Component\Dto\Actions;

class OpenSwooleConnectionActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::openSwooleConnection->value;
    }
    
    public string $type;
    public int $fd;
}