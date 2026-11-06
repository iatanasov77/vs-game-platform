<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;

class GameRoomDto
{
    public int $id;
    public string $owner;
    public string $guid;
    public string $name;
    
    public GameDto $game;
    public array $players; // PlayerDto[]
    public array $teams; // CardGameTeam[]
    
    public string $status;
    public bool $isPlaying;
}
