﻿<?php namespace App\Component\Dto;

use App\Component\Type\PlayerColor;

class PlayerDto
{
    public string $name;
    public PlayerColor $playerColor;
    public int $pointsLeft;
}