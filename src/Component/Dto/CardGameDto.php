<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardGameTeam;

class CardGameDto extends GameDto
{
    /** @var PlayerDto[] */
    public array $players;
    
    public array $validBids;
    public array $validCards;
    public array $bidHistory;
    public ?BidDto $contract;
    
    public ?PlayerPosition $DummyPlayer;
    public ?PlayerPosition $DummyOwner;
    public ?PlayerPosition $currentPlayer;
    
    public PlayerPosition $FirstToPlayInTheRound;
    public int $RoundNumber;
    public int $TrickNumber;
    
    public int $SouthNorthPoints;
    public int $EastWestPoints;
    public CardGameTeam $winner = CardGameTeam::Neither;
    
    public Collection $MyCards;
    public array $Bids;
    public bool $LastBid = false;
}
