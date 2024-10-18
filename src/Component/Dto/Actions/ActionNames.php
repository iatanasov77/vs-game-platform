<?php namespace App\Component\Dto\Actions;

enum ActionNames: int
{
    case gameCreated        = 1;
    case dicesRolled        = 2;
    case movesMade          = 3;
    case gameEnded          = 4;
    case opponentMove       = 5;
    case undoMove           = 6;
    case connectionInfo     = 7;
    case gameRestore        = 8;
    case resign             = 9;
    case exitGame           = 10;
    case requestedDoubling  = 11;
    case acceptedDoubling   = 12;
    case rolled             = 13;
    case requestHint        = 14;
    case hintMoves          = 15;
}
