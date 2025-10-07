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
    
    public int $RoundNumber;
    public PlayerPosition $FirstToPlayInTheRound;
    
    public int $SouthNorthPoints;
    public int $EastWestPoints;
    
    public Collection $MyCards;
    public array $Bids;
    
    public array $deck;
    public array $pile;
    public array $teamsTricks;
}
