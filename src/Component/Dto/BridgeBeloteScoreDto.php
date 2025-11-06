<?php namespace App\Component\Dto;

class BridgeBeloteScoreDto
{
    public ?BidDto $contract;
    
    public int $SouthNorthPoints;
    
    public int $SouthNorthTotalInRoundPoints;
    
    public int $EastWestPoints;
    
    public int $EastWestTotalInRoundPoints;
}
