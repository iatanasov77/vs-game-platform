<?php namespace App\Component\Rules\CardGame\BridgeBeloteGameMechanics;

use App\Component\Rules\CardGame\Bid;

class RoundResult
{
    public function __construct( Bid $contract )
    {
        $this->Contract = $contract;
    }
    
    public Bid $Contract;
    
    public int $SouthNorthPoints = 0;
    
    public int $SouthNorthTotalInRoundPoints = 0;
    
    public int $EastWestPoints = 0;
    
    public int $EastWestTotalInRoundPoints = 0;
    
    public bool $NoTricksForOneOfTheTeams;
    
    public int $HangingPoints = 0;
}
