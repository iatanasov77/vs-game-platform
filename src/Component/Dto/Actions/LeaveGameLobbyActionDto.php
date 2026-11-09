<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\GameRoomDto;

class LeaveGameLobbyActionDto extends MercureActionDto
{
    public function __construct()
    {
        $this->actionName = MercureActionNames::leaveGameLobby->value;
    }
    
    public int $index;
    public GameRoomDto $room;
}
