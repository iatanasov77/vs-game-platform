<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;

class PointDto
{
    public int $blackNumber;
    public Collection $checkers;  // CheckerDto[]
    public int $whiteNumber;
}
