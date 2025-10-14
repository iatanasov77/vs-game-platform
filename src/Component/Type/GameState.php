<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum GameState: int
{
    case opponentConnectWaiting = 0;
    case firstThrow             = 1;
    case playing                = 2;
    case requestedDoubling      = 3;
    case ended                  = 4;
    
    // Card Games States
    case firstBid               = 5;
    case bidding                = 6;
    case firstRound             = 7;
}
    