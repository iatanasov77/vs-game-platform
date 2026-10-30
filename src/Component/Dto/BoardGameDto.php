<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;

class BoardGameDto extends GameDto
{
    /** @var PlayerDto[] */
    public array $players;
    
    public ?PlayerColor $currentPlayer;
    public PlayerColor $winner = PlayerColor::Neither;
    
    /** @var Collection | PointDto[] */
    public Collection $points;
    
    /** @var Collection | ChessSquareDto[] */
    public Collection $squares;
    
    /** @var Collection | MoveDto[] */
    public Collection  $validMoves;
    
    public ?PlayerColor $lastDoubler;
    public int $stake;
}
