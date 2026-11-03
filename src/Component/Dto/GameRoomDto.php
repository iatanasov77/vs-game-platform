<?php namespace App\Component\Dto;

class GameRoomDto
{
    public GameDto $game;
    
    public PlayerDto $owner;
    
    /** @var PlayerDto[] */
    public array $players;
}
