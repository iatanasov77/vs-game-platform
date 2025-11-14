<?php namespace App\Component\Dto;

use App\Component\Type\PlayerPosition;
use App\Component\Type\AnnounceType;

class AnnounceDto
{
    public AnnounceType $Type;
    
    public PlayerPosition $Player;
}
