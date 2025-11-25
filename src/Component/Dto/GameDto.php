<?php namespace App\Component\Dto;

use App\Component\Type\GameState;

class GameDto
{
    public string $id;
    public string $gameCode;
    
    public GameState $playState;
    public float $thinkTime;
    
    public int $goldMultiplier;
    public bool $isGoldGame;
}
