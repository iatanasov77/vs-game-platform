<?php namespace App\Component\Dto\editor;

use Doctrine\Common\Collections\Collection;
use App\Component\Dto\GameDto;
use App\Component\Dto\DiceDto;

class GameStringRequest
{
    public GameDto $game;
    
    public Collection $dice; // DiceDto[]
}
