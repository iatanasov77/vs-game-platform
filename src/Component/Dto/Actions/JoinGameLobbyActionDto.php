<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameRoomDto;

class JoinGameLobbyActionDto extends MercureActionDto
{
    public function __construct()
    {
        $this->actionName = MercureActionNames::joinGameLobby->value;
    }
    
    public GameRoomDto $room;
}
