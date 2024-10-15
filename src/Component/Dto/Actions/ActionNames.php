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
    case createGame         = 10;
    case exitGame           = 11;
    case requestedDoubling  = 12;
    case acceptedDoubling   = 13;
    case rolled             = 14;
    case requestHint        = 15;
    case hintMoves          = 16;
}
