<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;

class GameDto
{
    public string $id;
    public PlayerDto $blackPlayer;
    public PlayerDto $whitePlayer;
    public ?PlayerColor $currentPlayer;
    public PlayerColor $winner = PlayerColor::Neither;
    public GameState $playState;
    
    /** @var Collection | PointDto[] */
    public Collection $points;
    
    /** @var Collection | MoveDto[] */
    public Collection  $validMoves;
    
    public float $thinkTime;
    
    public int $goldMultiplier;
    public bool $isGoldGame;
    public ?PlayerColor $lastDoubler;
    public int $stake;
}
