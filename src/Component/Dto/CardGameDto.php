<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardGameTeam;
use App\Component\Rules\CardGame\Bid;

class CardGameDto extends GameDto
{
    /** @var PlayerDto[] */
    public array $players;
    
    public array $validBids;
    public array $validCards;
    public ?Bid $contract;
    
    public ?PlayerPosition $currentPlayer;
    public CardGameTeam $winner = CardGameTeam::Neither;
    
    public PlayerPosition $FirstToPlayInTheRound;
    public int $RoundNumber;
    public int $TrickNumber;
    
    public int $SouthNorthPoints;
    public int $EastWestPoints;
    
    public Collection $MyCards;
    public array $Bids;
}
