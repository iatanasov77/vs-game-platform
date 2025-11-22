<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;

class MoveDto
{
    public PlayerColor $color;
    public int $from;
    public int $to;
    public Collection $nextMoves; // MoveDto[]
    public bool $animate    = false;
    public bool $hint       = false;
}
