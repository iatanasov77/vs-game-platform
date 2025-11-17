<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;

class ChessMoveDto
{
    public PlayerColor $color;
    public string $from;
    public string $to;
    public Collection $nextMoves; // ChessMoveDto[]
    public bool $animate    = false;
    public bool $hint       = false;
}
