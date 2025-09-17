<?php namespace App\Component\Manager\GameMechanics;

use App\Component\Rules\CardGame\Bid;

class RoundResult
{
    public function __construct( Bid $contract )
    {
        $this->Contract = $contract;
    }
    
    public Bid $Contract;
    
    public int $SouthNorthPoints;
    
    public int $SouthNorthTotalInRoundPoints;
    
    public int $EastWestPoints;
    
    public int $EastWestTotalInRoundPoints;
    
    public bool $NoTricksForOneOfTheTeams;
    
    public int $HangingPoints;
}
