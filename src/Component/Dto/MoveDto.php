<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;

class MoveDto
{
    public PlayerColor $color;
    public int $from;
    public Collection $nextMoves; // MoveDto[]
    public int $to;
    public bool $animate    = false;
    public bool $hint       = false;
}
