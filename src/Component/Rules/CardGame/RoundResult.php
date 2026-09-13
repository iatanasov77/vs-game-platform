<?php namespace App\Component\Rules\CardGame;

use App\Component\Rules\CardGame\Bid;

class RoundResult
{
    public function __construct( Bid $contract )
    {
        $this->Contract = $contract;
    }
    
    public Bid $Contract;
}
