<?php namespace App\Component\Dto\Actions;

class CreateGameLobbyActionDto extends MercureActionDto
{
    public function __construct()
    {
        $this->actionName = MercureActionNames::createGameLobby->value;
    }
    
    public string $gameType;
}
