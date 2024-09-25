<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;

class GameDto
{
    public string $id;
    public PlayerDto $blackPlayer;
    public PlayerDto $whitePlayer;
    public PlayerColor $currentPlayer;
    public PlayerColor $winner = PlayerColor::Neither;
    public GameState $playState;
    public float $thinkTime;
    
    /** @var Collection | PointDto[] */
    public Collection $points;
    
    /** @var Collection | MoveDto[] */
    public Collection  $validMoves;
}