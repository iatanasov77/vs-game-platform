<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum GameState: string
{
    case OpponentConnectWaiting = 'opponentConnectWaiting';
    case FirstThrow             = 'firstThrow';
    case Playing                = 'playing';
    case Ended                  = 'ended';
}
    