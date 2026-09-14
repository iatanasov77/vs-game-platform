<?php namespace App\Component\Rules\CardGame\BridgeBeloteGameMechanics;

use App\Component\Rules\CardGame\RoundResult as BaseRoundResult;

class RoundResult extends BaseRoundResult
{
    public int $SouthNorthPoints = 0;
    
    public int $SouthNorthTotalInRoundPoints = 0;
    
    public int $EastWestPoints = 0;
    
    public int $EastWestTotalInRoundPoints = 0;
    
    public bool $NoTricksForOneOfTheTeams;
    
    public int $HangingPoints = 0;
}
