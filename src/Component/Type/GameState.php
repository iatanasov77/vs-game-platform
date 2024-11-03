<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum GameState: int
{
    // My Workaround State
    case Created                = 0;
    
    case OpponentConnectWaiting = 1;
    case FirstThrow             = 2;
    case Playing                = 3;
    case Ended                  = 4;
}
    