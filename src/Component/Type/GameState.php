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
    
    // Chess States
    case firstMove              = 5;
    
    // Card Games States
    case firstBid               = 6;
    case bidding                = 7;
    case firstRound             = 8;
    case roundEnded             = 9;
}
    