<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameRoomDto;

class GameLobbyCreatedActionDto extends MercureActionDto
{
    public function __construct()
    {
        $this->actionName = MercureActionNames::gameLobbyCreated->value;
    }
    
    public GameRoomDto $room;
}
