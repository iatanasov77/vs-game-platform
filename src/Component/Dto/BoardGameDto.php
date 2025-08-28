<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;

class BoardGameDto extends GameDto
{
    public PlayerDto $blackPlayer;
    public PlayerDto $whitePlayer;
    public ?PlayerColor $currentPlayer;
    public PlayerColor $winner = PlayerColor::Neither;
    
    /** @var Collection | PointDto[] */
    public Collection $points;
    
    /** @var Collection | MoveDto[] */
    public Collection  $validMoves;
    
    public ?PlayerColor $lastDoubler;
    public int $stake;
}
