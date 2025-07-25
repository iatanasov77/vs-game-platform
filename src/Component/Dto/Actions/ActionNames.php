<?php namespace App\Component\Dto\Actions;

enum ActionNames: int
{
    case gameCreated        = 0;
    case dicesRolled        = 1;
    case movesMade          = 2;
    case gameEnded          = 3;
    case opponentMove       = 4;
    case undoMove           = 5;
    case connectionInfo     = 6;
    case gameRestore        = 7;
    case resign             = 8;
    case exitGame           = 9;
    case requestedDoubling  = 10;
    case acceptedDoubling   = 11;
    case rolled             = 12;
    case requestHint        = 13;
    case hintMoves          = 14;
    
    case serverWasTerminated    = 15;
}
