<?php namespace App\Component\Dto;

use App\Component\Type\PlayerColor;

class PlayerDto
{
    public string $name;
    public ?PlayerColor $playerColor;
    public int $pointsLeft;
    public $photoUrl;
    public $gold;
    public $elo;
    
    // My Property to Detect If Player is AI in Frontend
    public bool $isAi;
}
