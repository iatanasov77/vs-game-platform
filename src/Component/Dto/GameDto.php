<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;

class GameDto
{
    public string $id;
    public PlayerDto $blackPlayer;
    public PlayerDto $whitePlayer;
    public PlayerColor $currentPlayer;
    public PlayerColor $winner = PlayerColor::neither;
    public GameState $playState;
    public float $thinkTime;
    
    /** @var Collection | PointDto[] */
    public Collection $points;
    
    /** @var Collection | MoveDto[] */
    public Collection  $validMoves;
}