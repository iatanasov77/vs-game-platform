<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\ConnectionDto;

class ConnectionInfoActionDto extends ActionDto
{
    public function  __construct()
    {
        $this->actionName = ActionNames::connectionInfo;
    }

    public ConnectionDto $connection;
}
