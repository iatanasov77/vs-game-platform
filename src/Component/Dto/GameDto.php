<?php namespace App\Component\Dto;

use App\Component\Type\GameState;

class GameDto
{
    public string $id;
    public string $gameCode;
    public string $title;
    public GameState $playState;
    
    /** @var PlayerDto[] */
    public array $players;
    public int $maxPlayers;
    
    public float $thinkTime;
    
    public int $goldMultiplier;
    public bool $isGoldGame;
}
