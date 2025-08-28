<?php namespace App\Component\Dto;

use App\Component\Type\PlayerPosition;

class CardGameDto extends GameDto
{
    public PlayerDto $northPlayer;
    public PlayerDto $eastPlayer;
    public PlayerDto $southPlayer;
    public PlayerDto $westPlayer;
    
    public ?PlayerPosition $currentPlayer;
    public PlayerPosition $winner = PlayerPosition::Neither;
}
