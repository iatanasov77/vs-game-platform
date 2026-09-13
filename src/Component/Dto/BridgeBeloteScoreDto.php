<?php namespace App\Component\Dto;

class BridgeBeloteScoreDto extends ScoreDto
{
    public int $SouthNorthPoints;
    
    public int $SouthNorthTotalInRoundPoints;
    
    public int $EastWestPoints;
    
    public int $EastWestTotalInRoundPoints;
}
