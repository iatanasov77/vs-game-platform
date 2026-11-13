<?php namespace App\Component\Dto\Actions;

class MercureDisconnectPlayerActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = MercureActionNames::disconnectPlayer->value;
    }
    
    public $player;
}